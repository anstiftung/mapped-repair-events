SELECT COUNT( uid ) , DATE_FORMAT( created, '%m.%Y' ) dform,  DATE_FORMAT( created, '%Y-%m' ) d
FROM events
GROUP BY DATE_FORMAT( created, '%m.%Y' )
ORDER BY d ASC LIMIT 0,100000;

SELECT COUNT( uid ) , DATE_FORMAT( created, '%m.%Y' ) dform,  DATE_FORMAT( created, '%Y-%m' ) d
FROM events
GROUP BY DATE_FORMAT( created, '%m.%Y' ) ORDER BY d ASC LIMIT 0,100000;

SELECT COUNT( uid ) , DATE_FORMAT( created, '%m.%Y' ) dform, DATE_FORMAT( created, '%Y-%m' ) d
FROM workshops
GROUP BY DATE_FORMAT( created, '%m.%Y' )
ORDER BY d ASC LIMIT 0,100000;

SELECT COUNT( id ) , DATE_FORMAT( created, '%m.%Y' ) dform, DATE_FORMAT( created, '%Y-%m' ) d
FROM worknews
WHERE confirm = 'ok'
GROUP BY DATE_FORMAT( created, '%m.%Y' )
ORDER BY d ASC LIMIT 0,100000;

SELECT COUNT( id ) , DATE_FORMAT( created, '%m.%Y' ) dform, DATE_FORMAT( created, '%Y-%m' ) d
FROM newsletters
WHERE
confirm = 'ok'
GROUP BY DATE_FORMAT( created, '%m.%Y' )
ORDER BY d ASC LIMIT 0,100000;
