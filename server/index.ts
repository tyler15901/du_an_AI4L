import express from 'express';
import 'dotenv/config';
import OpenAI from 'openai';
import mysql from 'mysql2/promise';

type Json = any;

const app = express();
app.use(express.json({ limit: '1mb' }));

// Basic CORS for local dev
app.use((req, res, next) => {
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET,POST,OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type');
  if (req.method === 'OPTIONS') return res.sendStatus(200);
  next();
});

const requiredEnv = ['DB_HOST','DB_PORT','DB_USER','DB_NAME','OPENAI_API_KEY'] as const;
for (const key of requiredEnv) {
  if (!process.env[key]) {
    console.warn(`[WARN] Missing env ${key}. Check your .env file.`);
  }
}

const pool = mysql.createPool({
  host: process.env.DB_HOST,
  port: Number(process.env.DB_PORT ?? 3306),
  user: process.env.DB_USER,
  password: process.env.DB_PASS,
  database: process.env.DB_NAME,
  connectionLimit: 10,
  namedPlaceholders: true
});

const useAI = Boolean(process.env.OPENAI_API_KEY);
let openai: any = null;
if (useAI) {
  openai = new OpenAI({ apiKey: process.env.OPENAI_API_KEY! });
}

type Major = {
  id: string;
  name: string;
  weightInterests?: Record<string, number>;
  weightSkills?: Record<string, number>;
  weightGrades?: Record<string, number>;
  constraints?: { minMath?: number };
};

async function getMajors(): Promise<Major[]> {
  const [rows] = await pool.query(
    'SELECT id, name, weight_interests, weight_skills, weight_grades, constraints_json FROM majors'
  );
  return (rows as any[]).map((row: any) => ({
    id: row.id,
    name: row.name,
    weightInterests: JSON.parse(row.weight_interests ?? '{}'),
    weightSkills: JSON.parse(row.weight_skills ?? '{}'),
    weightGrades: row.weight_grades ? JSON.parse(row.weight_grades) : {},
    constraints: row.constraints_json ? JSON.parse(row.constraints_json) : {}
  }));
}

function score(profile: Json, major: Major): number {
  let s = 0;
  for (const [k, w] of Object.entries(major.weightInterests ?? {})) s += (w as number) * (profile?.interests?.[k] ?? 0);
  for (const [k, w] of Object.entries(major.weightSkills ?? {})) s += (w as number) * (profile?.skills?.[k] ?? 0);
  for (const [k, w] of Object.entries(major.weightGrades ?? {})) s += (w as number) * (((profile?.grades?.[k] ?? 0) / 10) * 5);
  if (major.constraints?.minMath && (profile?.grades?.math ?? 0) < major.constraints.minMath) s -= 3;
  return s;
}

app.get('/health', async (_req, res) => {
  try {
    const [r] = await pool.query('SELECT 1 AS ok');
    res.json({ ok: true, db: r ? 'up' : 'unknown' });
  } catch (e) {
    res.status(500).json({ ok: false, error: String(e) });
  }
});

// Fetch majors for UI dropdowns or admin screens
app.get('/api/majors', async (_req, res) => {
  try {
    const majors = await getMajors();
    res.json({ majors });
  } catch (e) {
    res.status(500).json({ message: 'Internal error', error: String(e) });
  }
});

app.post('/api/recommend', async (req, res) => {
  const profile = req.body?.profile as Json;
  if (!profile) return res.status(400).json({ message: 'profile is required' });

  const conn = await pool.getConnection();
  try {
    await conn.beginTransaction();
    const [insertProfile] = await conn.execute(
      `INSERT INTO profiles (age, location, finance_level, interests, skills, grades, career_goals, personality)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?)`,
      [
        profile.age ?? null,
        profile.location ?? null,
        profile.financeLevel ?? null,
        JSON.stringify(profile.interests ?? {}),
        JSON.stringify(profile.skills ?? {}),
        JSON.stringify(profile.grades ?? {}),
        JSON.stringify(profile.careerGoals ?? {}),
        JSON.stringify(profile.personality ?? {})
      ]
    );
    const profileId = (insertProfile as any).insertId;

    const majors = await getMajors();
    const top = majors
      .map(m => ({ m, score: score(profile, m) }))
      .sort((a, b) => b.score - a.score)
      .slice(0, 5)
      .map(x => ({ majorId: x.m.id, name: x.m.name, score: Number(x.score.toFixed(2)) }));

    let reason = 'AI disabled (no OPENAI_API_KEY). Đây là giải thích mặc định dựa trên điểm quy tắc.';
    if (useAI) {
      const ai = await openai.chat.completions.create({
        model: 'gpt-4o-mini',
        temperature: 0.4,
        messages: [
          { role: 'system', content: 'Bạn là cố vấn hướng nghiệp cho sinh viên Việt Nam, trả lời ngắn gọn, thực tế.' },
          { role: 'user', content: `Hồ sơ: ${JSON.stringify(profile)}\nTop ngành theo điểm: ${JSON.stringify(top)}` }
        ]
      });
      reason = ai.choices?.[0]?.message?.content ?? reason;
    }

    const [rec] = await conn.execute(
      'INSERT INTO recommendations (profile_id, ai_reason) VALUES (?, ?)',
      [profileId, reason]
    );
    const recId = (rec as any).insertId;

    const values = top.map(t => [recId, t.majorId, t.score]);
    if (values.length) {
      const placeholders = values.map(() => '(?,?,?)').join(',');
      const flat: (number | string)[] = values.flat();
      await conn.execute(
        `INSERT INTO recommendation_items (recommendation_id, major_id, score) VALUES ${placeholders}`,
        flat
      );
    }

    await conn.commit();
    res.json({ profileId, recommendationId: recId, recommendations: top, aiReason: reason });
  } catch (error) {
    await conn.rollback();
    console.error(error);
    res.status(500).json({ message: 'Internal error', error: String(error) });
  } finally {
    conn.release();
  }
});

app.get('/api/recommendations/:profileId', async (req, res) => {
  try {
    const profileId = Number(req.params.profileId);
    const [recs] = await pool.query(
      'SELECT * FROM recommendations WHERE profile_id=? ORDER BY id DESC',
      [profileId]
    );
    const [items] = await pool.query(
      'SELECT * FROM recommendation_items WHERE recommendation_id IN (SELECT id FROM recommendations WHERE profile_id=?)',
      [profileId]
    );
    res.json({ recommendations: recs, items });
  } catch (e) {
    res.status(500).json({ message: 'Internal error', error: String(e) });
  }
});

const PORT = Number(process.env.PORT ?? 3000);
app.listen(PORT, () => console.log(`API ready on http://localhost:${PORT}`));


