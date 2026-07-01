SELECT
    `f`.`uid` AS `Funding UID`,
    `w`.`uid` AS `Workshop UID`,
    `w`.`name` AS `Initiative`,
    `fu`.`question_text_a`,
    `fu`.`question_text_b`
FROM `fundingusageproofs` `fu`
INNER JOIN `fundings` `f` ON `f`.`fundingusageproof_id` = `fu`.`id`
LEFT JOIN `workshops` `w` ON `w`.`uid` = `f`.`workshop_uid`
ORDER BY `f`.`uid`;
