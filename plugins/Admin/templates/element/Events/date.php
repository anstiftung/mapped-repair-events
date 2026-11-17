<?php
declare(strict_types=1);

use Cake\Core\Configure;

/** @var \App\Model\Entity\Event $object */

echo '<span style="white-space: nowrap;">';
echo $object->datumstart->i18nFormat(Configure::read('DateFormat.de.DateLong2WithWeekday')) . ', ';
echo $object->uhrzeitstart->i18nFormat(Configure::read('DateFormat.de.TimeShort')) . ' - ' . $object->uhrzeitend->i18nFormat(Configure::read('DateFormat.de.TimeShort')) . ' Uhr';
echo '</span>';