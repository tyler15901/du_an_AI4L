USE duan_ai;

INSERT INTO majors (id,name,tags,weight_interests,weight_skills,weight_grades,constraints_json)
VALUES
('it','Công nghệ thông tin','["it"]','{"logic":2}','{"programming":3,"math":2,"analysis":2}','{"math":2,"informatics":3}','{"minMath":6}'),
('design','Thiết kế đồ họa','["design"]','{"creative":3}','{"design":3,"communication":1}',NULL,NULL),
('business','Quản trị kinh doanh','["business"]','{"business":3,"social":1}','{"communication":2,"analysis":1}',NULL,NULL)
ON DUPLICATE KEY UPDATE name=VALUES(name);


