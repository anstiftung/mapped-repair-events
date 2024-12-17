<?php

use Cake\Core\Configure;

if ($funding->money_transfer_date === null) {
    return;
}

echo '<div class="verification-wrapper is-verified">';
    echo 'Am ' . $funding->money_transfer_date->i18nFormat(Configure::read('DateFormat.de.DateShort')) . ' wurden ' . $this->MyNumber->formatAsDecimal($funding->budgetplan_total_with_limit) . ' € überwiesen.';
echo '</div>';
