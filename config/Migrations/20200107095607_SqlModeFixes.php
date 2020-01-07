<?php
use Migrations\AbstractMigration;

class SqlModeFixes extends AbstractMigration
{
    public function change()
    {
        $this->execute("
            ALTER TABLE `blocked_workshop_slugs` CHANGE `url` `url` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `status` `status` TINYINT(4) NULL DEFAULT NULL;
            ALTER TABLE `blogs` CHANGE `name` `name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `text` `text` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `url` `url` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
            ALTER TABLE `brands` CHANGE `name` `name` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_bin NULL DEFAULT NULL, CHANGE `status` `status` SMALLINT(6) NULL DEFAULT NULL, CHANGE `created` `created` DATETIME NULL DEFAULT NULL, CHANGE `modified` `modified` DATETIME NULL DEFAULT NULL;
            ALTER TABLE `categories` CHANGE `name` `name` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_bin NULL DEFAULT NULL, CHANGE `icon` `icon` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_bin NULL DEFAULT NULL, CHANGE `lft` `lft` INT(11) NULL DEFAULT NULL, CHANGE `rght` `rght` INT(11) NULL DEFAULT NULL, CHANGE `status` `status` SMALLINT(6) NULL DEFAULT NULL, CHANGE `created` `created` DATETIME NULL DEFAULT NULL, CHANGE `modified` `modified` DATETIME NULL DEFAULT NULL;
            ALTER TABLE `countries` CHANGE `name_en` `name_en` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL, CHANGE `name_de` `name_de` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL, CHANGE `rank` `rank` INT(4) NULL DEFAULT NULL;
            ALTER TABLE `events` CHANGE `eventbeschreibung` `eventbeschreibung` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `datumstart` `datumstart` DATE NULL DEFAULT NULL, CHANGE `uhrzeitstart` `uhrzeitstart` TIME NULL DEFAULT NULL, CHANGE `uhrzeitend` `uhrzeitend` TIME NULL DEFAULT NULL, CHANGE `veranstaltungsort` `veranstaltungsort` VARCHAR(99) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `strasse` `strasse` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `zip` `zip` VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `ort` `ort` VARCHAR(99) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `author` `author` VARCHAR(99) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `land` `land` VARCHAR(99) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `lat` `lat` DOUBLE NULL DEFAULT 0, CHANGE `lng` `lng` DOUBLE NULL DEFAULT 0, CHANGE `image` `image` VARCHAR(150) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `image_alt_text` `image_alt_text` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `status` `status` SMALLINT(6) NULL DEFAULT NULL, CHANGE `owner` `owner` INT(8) UNSIGNED NULL DEFAULT NULL, CHANGE `created` `created` DATETIME NULL DEFAULT NULL, CHANGE `updated` `updated` DATETIME NULL DEFAULT NULL, CHANGE `updated_by` `updated_by` INT(8) UNSIGNED NULL DEFAULT NULL, CHANGE `currently_updated_by` `currently_updated_by` INT(8) UNSIGNED NULL DEFAULT NULL, CHANGE `currently_updated_start` `currently_updated_start` DATETIME NULL DEFAULT NULL, CHANGE `lang` `lang` VARCHAR(3) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `workshop_uid` `workshop_uid` INT(8) UNSIGNED NULL DEFAULT NULL;
            ALTER TABLE `form_fields` CHANGE `name` `name` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `identifier` `identifier` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
            ALTER TABLE `form_field_options` CHANGE `form_field_id` `form_field_id` INT(10) UNSIGNED NULL DEFAULT NULL, CHANGE `value` `value` INT(10) UNSIGNED NULL DEFAULT NULL, CHANGE `name` `name` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `rank` `rank` INT(10) UNSIGNED NULL DEFAULT NULL;
            ALTER TABLE `groups` CHANGE `name` `name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
            ALTER TABLE `info_sheets` CHANGE `device_name` `device_name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `no_repair_reason_text` `no_repair_reason_text` VARCHAR(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `status` `status` SMALLINT(6) NULL DEFAULT NULL, CHANGE `owner` `owner` INT(8) UNSIGNED NULL DEFAULT NULL, CHANGE `created` `created` DATETIME NULL DEFAULT NULL, CHANGE `updated` `updated` DATETIME NULL DEFAULT NULL, CHANGE `updated_by` `updated_by` INT(8) UNSIGNED NULL DEFAULT NULL, CHANGE `currently_updated_by` `currently_updated_by` INT(8) UNSIGNED NULL DEFAULT NULL, CHANGE `currently_updated_start` `currently_updated_start` DATETIME NULL DEFAULT NULL, CHANGE `event_uid` `event_uid` INT(8) UNSIGNED NULL DEFAULT NULL, CHANGE `category_id` `category_id` INT(10) NULL DEFAULT NULL, CHANGE `brand_id` `brand_id` INT(10) UNSIGNED NULL DEFAULT NULL;
            ALTER TABLE `metatags` CHANGE `object_uid` `object_uid` INT(10) UNSIGNED NULL DEFAULT NULL, CHANGE `title` `title` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `description` `description` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `keywords` `keywords` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
            ALTER TABLE `newsletters` CHANGE `email` `email` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `plz` `plz` VARCHAR(8) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `created` `created` DATETIME NULL DEFAULT NULL;
            ALTER TABLE `pages` CHANGE `text` `text` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `status` `status` SMALLINT(6) NULL DEFAULT NULL, CHANGE `owner` `owner` INT(8) UNSIGNED NULL DEFAULT NULL, CHANGE `created` `created` DATETIME NULL DEFAULT NULL, CHANGE `updated` `updated` DATETIME NULL DEFAULT NULL, CHANGE `updated_by` `updated_by` INT(8) UNSIGNED NULL DEFAULT NULL, CHANGE `currently_updated_by` `currently_updated_by` INT(8) UNSIGNED NULL DEFAULT NULL, CHANGE `currently_updated_start` `currently_updated_start` DATETIME NULL DEFAULT NULL, CHANGE `lang` `lang` VARCHAR(3) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `position` `position` INT(10) UNSIGNED NULL DEFAULT NULL, CHANGE `parent_uid` `parent_uid` INT(10) UNSIGNED NULL DEFAULT NULL, CHANGE `lft` `lft` INT(10) NULL DEFAULT NULL, CHANGE `rght` `rght` INT(10) NULL DEFAULT NULL;
            ALTER TABLE `photos` CHANGE `object_type` `object_type` ENUM('pages','posts','workshops','events') CHARACTER SET ascii COLLATE ascii_bin NULL DEFAULT NULL, CHANGE `status` `status` SMALLINT(6) NULL DEFAULT NULL, CHANGE `owner` `owner` INT(8) UNSIGNED NULL DEFAULT NULL, CHANGE `created` `created` DATETIME NULL DEFAULT NULL, CHANGE `updated` `updated` DATETIME NULL DEFAULT NULL, CHANGE `updated_by` `updated_by` INT(8) UNSIGNED NULL DEFAULT NULL, CHANGE `currently_updated_by` `currently_updated_by` INT(8) UNSIGNED NULL DEFAULT NULL, CHANGE `currently_updated_start` `currently_updated_start` DATETIME NULL DEFAULT NULL;
            ALTER TABLE `posts` CHANGE `url` `url` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `city` `city` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `author` `author` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `image` `image` VARCHAR(150) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `image_alt_text` `image_alt_text` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `publish` `publish` DATE NULL DEFAULT NULL, CHANGE `status` `status` SMALLINT(6) NULL DEFAULT NULL, CHANGE `owner` `owner` INT(8) UNSIGNED NULL DEFAULT NULL, CHANGE `created` `created` DATETIME NULL DEFAULT NULL, CHANGE `updated` `updated` DATETIME NULL DEFAULT NULL, CHANGE `updated_by` `updated_by` INT(8) UNSIGNED NULL DEFAULT NULL, CHANGE `currently_updated_by` `currently_updated_by` INT(8) UNSIGNED NULL DEFAULT NULL, CHANGE `currently_updated_start` `currently_updated_start` DATETIME NULL DEFAULT NULL;
            ALTER TABLE `posts` CHANGE `text` `text` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
            ALTER TABLE `roots` CHANGE `object_type` `object_type` ENUM('pages','users','posts','workshops','events','coaches','votings','photos','info_sheets') CHARACTER SET ascii COLLATE ascii_bin NULL DEFAULT NULL;
            ALTER TABLE `skills` CHANGE `name` `name` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_bin NULL DEFAULT NULL, CHANGE `status` `status` SMALLINT(6) NULL DEFAULT NULL, CHANGE `created` `created` DATETIME NULL DEFAULT NULL, CHANGE `modified` `modified` DATETIME NULL DEFAULT NULL;
            ALTER TABLE `third_party_statistics` CHANGE `category_id` `category_id` INT(10) UNSIGNED NULL DEFAULT NULL, CHANGE `date_from` `date_from` DATE NULL DEFAULT NULL, CHANGE `date_to` `date_to` DATE NULL DEFAULT NULL, CHANGE `repaired` `repaired` INT(10) UNSIGNED NULL DEFAULT NULL;
            ALTER TABLE `users` CHANGE `email` `email` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `about_me` `about_me` VARCHAR(999) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `website` `website` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `street` `street` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `city` `city` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `country_code` `country_code` CHAR(2) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `phone` `phone` VARCHAR(24) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `twitter_username` `twitter_username` VARCHAR(99) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `feed_url` `feed_url` VARCHAR(99) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `facebook_username` `facebook_username` VARCHAR(99) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `additional_contact` `additional_contact` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `status` `status` SMALLINT(6) NULL DEFAULT NULL, CHANGE `owner` `owner` INT(8) UNSIGNED NULL DEFAULT NULL, CHANGE `created` `created` DATETIME NULL DEFAULT NULL, CHANGE `updated` `updated` DATETIME NULL DEFAULT NULL, CHANGE `updated_by` `updated_by` INT(8) UNSIGNED NULL DEFAULT NULL, CHANGE `currently_updated_by` `currently_updated_by` INT(8) UNSIGNED NULL DEFAULT NULL, CHANGE `currently_updated_start` `currently_updated_start` DATETIME NULL DEFAULT NULL, CHANGE `image` `image` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `image_alt_text` `image_alt_text` VARCHAR(99) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `lang` `lang` VARCHAR(3) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
            ALTER TABLE `users` CHANGE `private` `private` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `confirm` `confirm` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
            ALTER TABLE `users_workshops` CHANGE `created` `created` DATETIME NULL DEFAULT NULL, CHANGE `approved` `approved` DATETIME NULL DEFAULT NULL;
            ALTER TABLE `worknews` CHANGE `created` `created` DATETIME NULL DEFAULT NULL, CHANGE `modified` `modified` DATETIME NULL DEFAULT NULL;
            ALTER TABLE `worknews` CHANGE `confirm` `confirm` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `unsub` `unsub` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
            ALTER TABLE `workshops` DROP `currently_updated_start`;
            ALTER TABLE `workshops` ADD `currently_updated_start` DATETIME NULL DEFAULT NULL AFTER `currently_updated_by`;
            ALTER TABLE `workshops` CHANGE `url` `url` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `zip` `zip` VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `street` `street` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `city` `city` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `adresszusatz` `adresszusatz` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `country_code` `country_code` CHAR(2) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `email` `email` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `feed_url` `feed_url` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `twitter_username` `twitter_username` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `facebook_username` `facebook_username` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `additional_contact` `additional_contact` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `image` `image` VARCHAR(150) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `image_alt_text` `image_alt_text` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `lat` `lat` DOUBLE NULL DEFAULT 0, CHANGE `lng` `lng` DOUBLE NULL DEFAULT 0, CHANGE `traeger` `traeger` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `rechtsform` `rechtsform` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `rechtl_vertret` `rechtl_vertret` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `status` `status` SMALLINT(6) NULL DEFAULT NULL, CHANGE `owner` `owner` INT(8) UNSIGNED NULL DEFAULT NULL, CHANGE `created` `created` DATETIME NULL DEFAULT NULL, CHANGE `updated` `updated` DATETIME NULL DEFAULT NULL, CHANGE `updated_by` `updated_by` INT(8) UNSIGNED NULL DEFAULT NULL, CHANGE `currently_updated_by` `currently_updated_by` INT(8) UNSIGNED NULL DEFAULT NULL, CHANGE `lang` `lang` VARCHAR(3) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
            ALTER TABLE `workshops` CHANGE `text` `text` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
            ALTER TABLE `workshops` CHANGE `other_users` `other_users` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
        ");
        
    }
}
