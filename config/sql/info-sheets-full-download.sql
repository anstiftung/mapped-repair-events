SELECT

    i.uid as "uid_laufzettel",
    " " as "id",
    "anstiftung" as "data_provider",
    country.iso3 as "country",
    pc.name as "partner_product_category_main",
    c.name as "partner_product_category_sub",
    i.device_name as "partner_product_category_device",
    oc.name as "product_category",
    b.name as "brand",
    DATE_FORMAT(i.created, '%Y') - i.device_age as "year_of_manufacture",
    TRIM(
        CONCAT(
            COALESCE(ffoeiB.repair_status, ''), ' ',
            COALESCE(ffoeiC.repair_status, ''), ' ',
            COALESCE(ffoeiD.repair_status, ''), ' ',
            COALESCE(ffoeiE.repair_status, '')
        )
    ) as "repair_status",
    TRIM(
        CONCAT(
            COALESCE(ffoeiB.repair_barrier_if_end_of_life, ''), ' ',
            COALESCE(ffoeiC.repair_barrier_if_end_of_life, ''), ' ',
            COALESCE(ffoeiD.repair_barrier_if_end_of_life, ''), ' ',
            COALESCE(ffoeiE.repair_barrier_if_end_of_life, '')
        )
    ) as "repair_barrier_if_end_of_life",
    defect_found_ffo3.name as "defect_found_reason",
    defect_found_ffo4.name as "repair_postponed_reason",
    defect_found_ffo5.name as "no_repair_reason",
    no_repair_reason_text as "no_repair_reason_text",
    defect_found_ffo6.name as "device_must_not_be_used_anymore",
    w.uid as "group_identifier",
    e.datumstart as "event_date",
    DATE_FORMAT(i.updated, '%Y-%m-%d') as "record_date",
    defect_description as "problem"

FROM info_sheets i

LEFT JOIN brands b ON i.brand_id = b.id
LEFT JOIN categories c ON c.id = i.category_id
LEFT JOIN categories pc ON pc.id = c.parent_id
LEFT JOIN ords_categories oc ON c.ords_category_id = oc.id
JOIN events e ON e.uid = i.event_uid
JOIN workshops w ON e.workshop_uid = w.uid
LEFT JOIN countries country ON w.country_code = country.code
LEFT JOIN form_field_options defect_found_ffo3 ON defect_found_ffo3.form_field_id = 3 AND defect_found_ffo3.value = i.defect_found_reason
LEFT JOIN form_field_options defect_found_ffo4 ON defect_found_ffo4.form_field_id = 4 AND defect_found_ffo4.value = i.repair_postponed_reason
LEFT JOIN form_field_options defect_found_ffo5 ON defect_found_ffo5.form_field_id = 5 AND defect_found_ffo5.value = i.no_repair_reason
LEFT JOIN form_field_options defect_found_ffo6 ON defect_found_ffo6.form_field_id = 6 AND defect_found_ffo6.value = i.device_must_not_be_used_anymore
LEFT JOIN form_field_options_extra_infos ffoeiB ON ffoeiB.form_field_options_id = defect_found_ffo3.id
LEFT JOIN form_field_options_extra_infos ffoeiC ON ffoeiC.form_field_options_id = defect_found_ffo4.id
LEFT JOIN form_field_options_extra_infos ffoeiD ON ffoeiD.form_field_options_id = defect_found_ffo5.id
LEFT JOIN form_field_options_extra_infos ffoeiE ON ffoeiE.form_field_options_id = defect_found_ffo6.id

WHERE 1
AND i.status = 1
ORDER by e.datumstart DESC
