SELECT 
    w.uid,
    w.name,
    w.status,
    COUNT(i.uid) AS infosheet_count
FROM 
    workshops w
LEFT JOIN 
    events e ON e.workshop_uid = w.uid
LEFT JOIN 
    info_sheets i ON i.event_uid = e.uid
GROUP BY 
    w.uid, w.name, w.status
HAVING infosheet_count > 0
ORDER BY 
    infosheet_count DESC, w.name ASC
LIMIT 1000;