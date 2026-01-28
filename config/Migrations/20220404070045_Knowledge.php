<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class Knowledge extends BaseMigration
{
    public function change(): void
    {
        $sql = "ALTER TABLE `roots` CHANGE `object_type` `object_type` ENUM('pages','users','posts','workshops','events','coaches','votings','photos','info_sheets','knowledges') CHARACTER SET ascii COLLATE ascii_bin NULL DEFAULT NULL;";
        $this->execute($sql);

        $sql = "CREATE TABLE `knowledges` (
              `_id` int NOT NULL,
              `uid` int UNSIGNED DEFAULT NULL,
              `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
              `text` text,
              `status` smallint DEFAULT NULL,
              `owner` int UNSIGNED DEFAULT NULL,
              `created` datetime DEFAULT NULL,
              `updated` datetime DEFAULT NULL,
              `updated_by` int UNSIGNED DEFAULT NULL,
              `currently_updated_by` int UNSIGNED DEFAULT NULL,
              `currently_updated_start` datetime DEFAULT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

            CREATE TABLE `knowledges_categories` (
              `knowledge_uid` int UNSIGNED NOT NULL,
              `category_id` int UNSIGNED NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

            CREATE TABLE `knowledges_skills` (
              `knowledge_uid` int UNSIGNED NOT NULL,
              `skill_id` int UNSIGNED NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

            ALTER TABLE `knowledges`
              ADD PRIMARY KEY (`_id`),
              ADD KEY `uid` (`uid`);

            ALTER TABLE `knowledges_categories`
              ADD PRIMARY KEY (`knowledge_uid`,`category_id`),
              ADD KEY `knowledge_uid` (`knowledge_uid`),
              ADD KEY `category_id` (`category_id`);

            ALTER TABLE `knowledges_skills`
              ADD PRIMARY KEY (`knowledge_uid`,`skill_id`),
              ADD KEY `knowledge_uid` (`knowledge_uid`),
              ADD KEY `skill_id` (`skill_id`);

            ALTER TABLE `knowledges`
              MODIFY `_id` int NOT NULL AUTO_INCREMENT;
            COMMIT;";
        $this->execute($sql);
    }
}
