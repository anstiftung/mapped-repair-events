SELECT
    i.uid as "uid",
    pc.name as "main category",
    c.name as "sub category",
    b.name as "brand",
    i.device_name as "device",
    DATE_FORMAT(i.created, '%Y') - i.device_age as "construction year",
    e.datumstart as "date of event",
    defect_description as "defect description",
    defect_found_ffo2.name as "defect found name",
    defect_found_ffo3.name as "defect_found_reason",
    defect_found_ffo4.name as "repair_postponed_reason",
    defect_found_ffo5.name as "no_repair_reason",
    defect_found_ffo6.name as "device_must_not_be_used_anymore",
    no_repair_reason_text as "no_repair_reason_text",
    w.uid as "workshop uid",
    TRIM(
        CONCAT(
            COALESCE(ffoeiA.repair_status, ''), ' ',
            COALESCE(ffoeiB.repair_status, ''), ' ',
            COALESCE(ffoeiC.repair_status, ''), ' ',
            COALESCE(ffoeiD.repair_status, ''), ' ',
            COALESCE(ffoeiE.repair_status, '')
        )
    ) as "repair_status",
    TRIM(
        CONCAT(
            COALESCE(ffoeiA.repair_barrier_if_end_of_life, ''), ' ',
            COALESCE(ffoeiB.repair_barrier_if_end_of_life, ''), ' ',
            COALESCE(ffoeiC.repair_barrier_if_end_of_life, ''), ' ',
            COALESCE(ffoeiD.repair_barrier_if_end_of_life, ''), ' ',
            COALESCE(ffoeiE.repair_barrier_if_end_of_life, '')
        )
    ) as "repair_barrier_if_end_of_life"
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
LEFT JOIN form_field_options_extra_info ffoeiA ON ffoeiA.form_field_options_id = defect_found_ffo2.id
LEFT JOIN form_field_options_extra_info ffoeiB ON ffoeiB.form_field_options_id = defect_found_ffo3.id
LEFT JOIN form_field_options_extra_info ffoeiC ON ffoeiC.form_field_options_id = defect_found_ffo4.id
LEFT JOIN form_field_options_extra_info ffoeiD ON ffoeiD.form_field_options_id = defect_found_ffo5.id
LEFT JOIN form_field_options_extra_info ffoeiE ON ffoeiE.form_field_options_id = defect_found_ffo6.id
WHERE 1
AND i.status = 1
AND e.datumstart <= '2021-09-30'
ORDER by e.datumstart DESC
