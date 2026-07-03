SELECT 'Durch die Förderung können/konnten wir Anschaffungen tätigen, die unser Spektrum an Reparier-Angeboten erweitern' AS `Frage`,
    CASE `question_radio_a`
        WHEN 0 THEN 'trifft voll zu'
        WHEN 1 THEN 'trifft eher zu'
        WHEN 2 THEN 'trifft eher nicht zu'
        WHEN 3 THEN 'trifft überhaupt nicht zu'
        WHEN 4 THEN 'keine Angabe'
        ELSE 'keine Antwort'
    END AS `Antwort`,
    COUNT(*) AS `Anzahl`
FROM `fundingusageproofs`
GROUP BY `question_radio_a`

UNION ALL

SELECT 'Durch die Förderung können/konnten wir Fähigkeiten und Kompetenzen erwerben oder ausbauen, die unser Spektrum an Reparier-Angeboten erweitern',
    CASE `question_radio_b`
        WHEN 0 THEN 'trifft voll zu'
        WHEN 1 THEN 'trifft eher zu'
        WHEN 2 THEN 'trifft eher nicht zu'
        WHEN 3 THEN 'trifft überhaupt nicht zu'
        WHEN 4 THEN 'keine Angabe'
        ELSE 'keine Antwort'
    END,
    COUNT(*)
FROM `fundingusageproofs`
GROUP BY `question_radio_b`

UNION ALL

SELECT 'Durch die Förderung können/konnten wir mehr Veranstaltungen verwirklichen',
    CASE `question_radio_c`
        WHEN 0 THEN 'trifft voll zu'
        WHEN 1 THEN 'trifft eher zu'
        WHEN 2 THEN 'trifft eher nicht zu'
        WHEN 3 THEN 'trifft überhaupt nicht zu'
        WHEN 4 THEN 'keine Angabe'
        ELSE 'keine Antwort'
    END,
    COUNT(*)
FROM `fundingusageproofs`
GROUP BY `question_radio_c`

UNION ALL

SELECT 'Es kommen seitdem mehr Menschen zu den Veranstaltungen',
    CASE `question_radio_d`
        WHEN 0 THEN 'trifft voll zu'
        WHEN 1 THEN 'trifft eher zu'
        WHEN 2 THEN 'trifft eher nicht zu'
        WHEN 3 THEN 'trifft überhaupt nicht zu'
        WHEN 4 THEN 'keine Angabe'
        ELSE 'keine Antwort'
    END,
    COUNT(*)
FROM `fundingusageproofs`
GROUP BY `question_radio_d`

UNION ALL

SELECT 'Die Förderung trägt dazu bei, unsere Reparatur-Initiative/Selbsthilfewerkstatt zu sichern/zu verstetigen',
    CASE `question_radio_e`
        WHEN 0 THEN 'trifft voll zu'
        WHEN 1 THEN 'trifft eher zu'
        WHEN 2 THEN 'trifft eher nicht zu'
        WHEN 3 THEN 'trifft überhaupt nicht zu'
        WHEN 4 THEN 'keine Angabe'
        ELSE 'keine Antwort'
    END,
    COUNT(*)
FROM `fundingusageproofs`
GROUP BY `question_radio_e`

UNION ALL

SELECT 'Wie zufrieden wart ihr mit der Durchführung des Förderprogramms?',
    CASE `question_radio_f`
        WHEN 0 THEN 'trifft voll zu'
        WHEN 1 THEN 'trifft eher zu'
        WHEN 2 THEN 'trifft eher nicht zu'
        WHEN 3 THEN 'trifft überhaupt nicht zu'
        WHEN 4 THEN 'keine Angabe'
        ELSE 'keine Antwort'
    END,
    COUNT(*)
FROM `fundingusageproofs`
GROUP BY `question_radio_f`

ORDER BY `Frage`, `Antwort`;