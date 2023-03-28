SELECT COUNT(*) as Anzahl, city AS Stadt FROM workshops
WHERE status = 1
GROUP BY city
ORDER by Anzahl DESC, city ASC
LIMIT 10000;