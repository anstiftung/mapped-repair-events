SELECT
    e.eventbeschreibung as Description,
    w.name as Name,
    CONCAT(e.strasse, ", ", e.zip, " ", e.ort) as Address,
    e.ort as City,
    e.veranstaltungsort as AdditionalLocationInfo,
    CONCAT('https://www.reparatur-initiativen.de/', w.url, '?event=', e.uid, ',', e.datumstart) as Url,
    e.lat,
    e.lng
FROM
    events e
JOIN workshops w ON e.workshop_uid = w.uid
WHERE
    (e.datumstart BETWEEN '2019-10-18' AND '2019-10-20')
AND e.status = 1
AND w.status = 1
;
