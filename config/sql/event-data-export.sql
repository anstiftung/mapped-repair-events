SELECT
    e.eventbeschreibung as Description,
    e.datumstart as Date,
    w.name as Name,
    CONCAT(e.strasse, ", ", e.zip, " ", e.ort) as Address,
    e.ort as City,
    e.veranstaltungsort as AdditionalLocationInfo,
    CONCAT('https://www.reparatur-initiativen.de/', w.url, '?event=', e.uid, ',', e.datumstart) as Url,
    e.lat,
    e.lng,
    e.is_online_event as Online
FROM
    events e
JOIN workshops w ON e.workshop_uid = w.uid
WHERE
    (e.datumstart BETWEEN '2023-10-16' AND '2023-10-26')
AND e.status = 1
AND w.status = 1
ORDER BY e.datumstart ASC
LIMIT 10000
;
