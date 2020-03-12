SELECT
    i.uid as "Laufzettel-UID",
    pc.name as Oberkategorie,
    c.name as Unterkategorie,
    b.name as Marke,
    i.device_name as "Gerät/Gegenstand",
    DATE_FORMAT(i.created, '%Y') - i.device_age as "Alter in Jahren",
    DATE_FORMAT(e.datumstart, '%Y-%m-%d') as "Datum Reparaturveranstaltung",
    defect_description as Fehlerbeschreibung,
    defect_found_ffo2.name as "Fehler gefunden?",
    defect_found_ffo3.name as Reparaturerfolg,
    defect_found_ffo4.name as "Reparatur: vertagt",
    defect_found_ffo5.name as "Reparatur: nicht erfolgt",
    defect_found_ffo6.name as "Reparatur: Abbruch",
    no_repair_reason_text as "Keine Reparatur, weil",
    CONCAT('https://www.reparatur-initiativen.de/', w.url, '?event=', e.uid, ',', e.datumstart) as Url
FROM info_sheets i
LEFT JOIN brands b ON i.brand_id = b.id
LEFT JOIN categories c ON c.id = i.category_id
LEFT JOIN categories pc ON pc.id = c.parent_id
JOIN events e ON e.uid = i.event_uid
JOIN workshops w ON e.workshop_uid = w.uid
LEFT JOIN form_field_options defect_found_ffo2 ON defect_found_ffo2.form_field_id = 2 AND defect_found_ffo2.value = i.defect_found
LEFT JOIN form_field_options defect_found_ffo3 ON defect_found_ffo3.form_field_id = 3 AND defect_found_ffo3.value = i.defect_found_reason
LEFT JOIN form_field_options defect_found_ffo4 ON defect_found_ffo4.form_field_id = 4 AND defect_found_ffo4.value = i.repair_postponed_reason
LEFT JOIN form_field_options defect_found_ffo5 ON defect_found_ffo5.form_field_id = 5 AND defect_found_ffo5.value = i.no_repair_reason
LEFT JOIN form_field_options defect_found_ffo6 ON defect_found_ffo6.form_field_id = 6 AND defect_found_ffo6.value = i.device_must_not_be_used_anymore
WHERE 1
AND i.status = 1
AND w.uid = :workshopUid
ORDER by e.datumstart DESC