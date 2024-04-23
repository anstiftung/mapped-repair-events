SELECT COUNT(*) as WorknewsCount, ws.uid, ws.name FROM workshops ws
JOIN worknews wn ON ws.uid = wn.workshop_uid
WHERE wn.confirm = 'ok'
AND ws.status = 1
GROUP BY ws.uid 
ORDER BY WorknewsCount DESC
LIMIT 10000;