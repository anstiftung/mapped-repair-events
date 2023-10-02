SELECT COUNT(DISTINCT(w.uid)) FROM events e
INNER JOIN workshops w on e.workshop_uid = w.uid
WHERE e.datumstart >= NOW()
AND w.status = 1
AND e.status = 1 
ORDER BY e.datumstart ASC
LIMIT 10000;