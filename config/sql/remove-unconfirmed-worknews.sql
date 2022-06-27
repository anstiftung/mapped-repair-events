DELETE FROM worknews
WHERE 1
AND confirm <> 'ok'
AND created < NOW() - INTERVAL 30 DAY
