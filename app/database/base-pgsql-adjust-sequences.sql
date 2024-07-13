SELECT setval('options_id_seq', coalesce(max(id),0) + 1, false) FROM options;
SELECT setval('role_id_seq', coalesce(max(id),0) + 1, false) FROM role;
SELECT setval('user_role_id_seq', coalesce(max(id),0) + 1, false) FROM user_role;