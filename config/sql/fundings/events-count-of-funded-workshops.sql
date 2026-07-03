SELECT COUNT(DISTINCT e.uid) AS event_count, 
       COUNT(DISTINCT w.uid) AS workshop_count
FROM events e
INNER JOIN workshops w ON w.uid = e.workshop_uid
INNER JOIN fundings f ON f.workshop_uid = w.uid
WHERE e.status = 1
AND e.datumstart BETWEEN '2026-01-01' AND '2026-12-31'
#AND e.datumstart BETWEEN '2025-03-31' AND '2026-03-31'
;
