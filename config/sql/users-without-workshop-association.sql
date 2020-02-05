SELECT u.uid, u.nick, u.firstname, u.lastname, u.email, u.created
FROM users u
LEFT JOIN users_workshops uw ON uw.user_uid = u.uid
WHERE uw.workshop_uid IS NULL
AND u.created <  '2015-12-31'
LIMIT 0 , 100000
