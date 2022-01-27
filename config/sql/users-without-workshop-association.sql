SELECT u.uid, u.nick, u.firstname, u.lastname, u.email, u.created
FROM users u
LEFT JOIN users_workshops uw ON uw.user_uid = u.uid
WHERE uw.workshop_uid IS NULL
AND u.status > -1
AND u.created <  '2019-01-01'
LIMIT 0 , 100000
