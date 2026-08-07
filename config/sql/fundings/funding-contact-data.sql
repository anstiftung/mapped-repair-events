SELECT
    w.uid AS `UID Initiative`,
    w.name AS `Name der Initiative`,
    w.zip AS `PLZ der Initiative`,
    w.city AS `Ort der Initiative`,
    w.email AS `E-Mail der Initiative`,
    u.uid AS `UID Ansprechperson der Initiative`,
    u.firstname AS `Vorname Ansprechperson der Initiative`,
    u.lastname AS `Nachname Ansprechperson der Initiative`,
    u.email AS `E-Mail-Adresse Ansprechperson der Initiative`,
    fs.name AS `Name Trägerorganisation`,
    fs.contact_firstname AS `Vorname Ansprechperson der Trägerorganisation`,
    fs.contact_lastname AS `Nachname der Ansprechperson der Trägerorgansiation`,
    fs.contact_email AS `E-Mail-Adresse Ansprechperson der Trägerorgansiation`
FROM fundings f
LEFT JOIN workshops w ON w.uid = f.workshop_uid
LEFT JOIN users u ON u.uid = f.owner
LEFT JOIN fundingsupporters fs ON fs.id = f.fundingsupporter_id
ORDER BY w.name ASC
LIMIT 10000
;