# last run on 2025-03-06

### START events ###
DELETE FROM events WHERE status = -1 AND updated < NOW() - INTERVAL 6 MONTH;

DELETE ec
FROM events_categories ec
LEFT JOIN events e ON ec.event_uid = e.uid
WHERE e.uid IS NULL;

DELETE r
FROM roots r
LEFT JOIN events e ON r.uid = e.uid AND r.object_type = 'events'
WHERE e.uid IS NULL AND r.object_type = 'events';
### END events ###

### START info_sheets ###
DELETE FROM info_sheets WHERE status = -1 AND updated < NOW() - INTERVAL 6 MONTH;

DELETE r
FROM roots r
LEFT JOIN info_sheets e ON r.uid = e.uid AND r.object_type = 'info_sheets'
WHERE e.uid IS NULL AND r.object_type = 'info_sheets';
### END info_sheets ###

### START workshops ###
DELETE FROM workshops WHERE status = -1 AND updated < NOW() - INTERVAL 6 MONTH;

DELETE wc
FROM workshops_categories wc
LEFT JOIN workshops w ON wc.workshop_uid = w.uid
WHERE w.uid IS NULL;

DELETE r
FROM roots r
LEFT JOIN workshops e ON r.uid = e.uid AND r.object_type = 'workshops'
WHERE e.uid IS NULL AND r.object_type = 'workshops';
### END workshops ###
