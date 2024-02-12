SELECT DISTINCT count(*) AS anzahl_laufzettel,
                w.uid,
                w.name,
                w.zip,
                w.city,
                w.street,
                w.adresszusatz,
                concat('https://www.reparatur-initiativen.de/', w.url) AS url
FROM info_sheets i
LEFT JOIN events e ON e.uid = i.event_uid
LEFT JOIN workshops w ON w.uid = e.workshop_uid
WHERE 1
  AND i.status = 1
GROUP BY w.uid
ORDER BY anzahl_laufzettel DESC
LIMIT 10000;