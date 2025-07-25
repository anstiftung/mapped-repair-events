<?php
declare(strict_types=1);

echo '<div class="verification-wrapper ' . $funding->confirmed_events_css_class . '">';
    echo '<p>' . $additionalTextBefore . $funding->confirmed_events_human_readable . '</p>';
echo '</div>';
