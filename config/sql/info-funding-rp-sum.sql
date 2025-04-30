SELECT
funding_uid,
workshop_uid
LEAST(SUM(fbp.amount), 3000) as sum_least
FROM fundingbudgetplans fbp
JOIN fundings f ON fbp.funding_uid = f.uid
JOIN workshops w ON f.workshop_uid = w.uid
WHERE w.province_id = 11
AND fbp.type > 0 AND fbp.description <> '' AND fbp.amount > 0
GROUP BY funding_uid
