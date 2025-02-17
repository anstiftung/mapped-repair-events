SELECT ws.uid, ws.name, ws.status, ws.url, wn.id, wn.email FROM workshops ws
JOIN worknews wn ON ws.uid = wn.workshop_uid
WHERE wn.confirm = 'ok'
AND ws.status = -1
LIMIT 10000;