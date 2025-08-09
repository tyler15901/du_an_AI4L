<?php
require 'config.php';
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Chat tư vấn AI</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="assets/style.css">
  <style>
    .chat-wrapper { max-width:900px; margin:20px auto; background:#fff; box-shadow:0 2px 8px rgba(0,0,0,.06); border-radius:8px; overflow:hidden; }
    .chat-header { padding:12px 16px; background:#0b7a75; color:#fff; }
    .chat-body { height:60vh; overflow:auto; padding:16px; background:#f6f7f9; }
    .msg { margin:10px 0; display:flex; }
    .msg.user { justify-content:flex-end; }
    .bubble { max-width:70%; padding:10px 14px; border-radius:12px; }
    .msg.user .bubble { background:#0b7a75; color:#fff; border-bottom-right-radius:4px; }
    .msg.ai .bubble { background:#e8f5f4; color:#123; border-bottom-left-radius:4px; }
    .chat-input { display:flex; gap:8px; padding:12px; background:#fff; border-top:1px solid #eee; }
    .chat-input input { flex:1; padding:10px; }
    .chat-input button { padding:10px 16px; }
  </style>
</head>
<body>
  <div class="chat-wrapper">
    <div class="chat-header">
      <strong>Chuyên gia Hướng nghiệp (AI)</strong>
    </div>
    <div id="chatBody" class="chat-body"></div>
    <div class="chat-input">
      <input id="message" type="text" placeholder="Nhập câu hỏi của bạn...">
      <button id="sendBtn">Gửi</button>
    </div>
  </div>

<script>
let conversationId = null;
const chatBody = document.getElementById('chatBody');
const input = document.getElementById('message');
const sendBtn = document.getElementById('sendBtn');

function appendMessage(role, text){
  const wrap = document.createElement('div');
  wrap.className = 'msg ' + (role === 'user' ? 'user' : 'ai');
  const b = document.createElement('div');
  b.className = 'bubble';
  b.textContent = text;
  wrap.appendChild(b);
  chatBody.appendChild(wrap);
  chatBody.scrollTop = chatBody.scrollHeight;
}

async function sendMessage(){
  const text = input.value.trim();
  if(!text) return;
  appendMessage('user', text);
  input.value = '';
  try{
    const res = await fetch('api_chat.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ conversation_id: conversationId, message: text })
    });
    const data = await res.json();
    conversationId = data.conversation_id;
    appendMessage('ai', data.reply);
  } catch(err){
    appendMessage('ai', 'Lỗi kết nối máy chủ: ' + err.message);
  }
}

sendBtn.addEventListener('click', sendMessage);
input.addEventListener('keydown', (e)=>{ if(e.key==='Enter'){ sendMessage(); }});

// Lời chào ban đầu
appendMessage('ai', 'Xin chào! Tôi là trợ lý hướng nghiệp AI. Hãy chia sẻ sở thích, điểm mạnh, điểm số/môn học bạn thích để tôi gợi ý ngành/nghề và lộ trình học.');
</script>
</body>
</html>


