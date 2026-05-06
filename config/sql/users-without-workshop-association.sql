SELECT u.uid, u.nick, u.firstname, u.lastname, u.email, u.created, u.updated, u.status
FROM users u
LEFT JOIN users_workshops uw ON uw.user_uid = u.uid
WHERE uw.workshop_uid IS NULL
AND u.created < '2024-01-01'
AND u.updated < '2025-01-01'
LIMIT 0 , 100000
