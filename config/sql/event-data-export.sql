SELECT
    e.eventbeschreibung as "event-name",
    e.datumstart as date,
    w.name as organisation,
    CONCAT(e.strasse, ", ", e.zip, " ", e.ort) as location,
    e.ort as "town/city",
    w.country_code as country,
    e.veranstaltungsort as "additional-info",
    CONCAT('https://www.reparatur-initiativen.de/', w.url, '?event=', e.uid, ',', e.datumstart) as "more-info-link",
    e.lat as latitude,
    e.lng as longitude
FROM
    events e
JOIN workshops w ON e.workshop_uid = w.uid
WHERE
    (e.datumstart BETWEEN '2025-10-11' AND '2025-10-26')
AND e.status = 1
AND w.status = 1
ORDER BY e.datumstart ASC
LIMIT 10000
;
