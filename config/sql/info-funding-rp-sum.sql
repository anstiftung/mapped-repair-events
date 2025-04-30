SELECT SUM(fbp.amount)
FROM fundingbudgetplans fbp
JOIN fundings f ON fbp.funding_uid = f.uid
JOIN workshops w ON f.workshop_uid = w.uid
WHERE w.province_id = 11
AND fbp.type > 0 AND fbp.description <> '' AND fbp.amount > 0