<?php

echo '<div class="verification-wrapper ' . $funding->zuwendungsbestaetigung_status_css_class . '">';
echo '<p>' . $additionalTextBefore . $funding->zuwendungsbestaetigung_status_human_readable . '</p>';
    if ($funding->zuwendungsbestaetigung_comment != '') {
        echo '<p class="comment">' . h($funding->zuwendungsbestaetigung_comment) . '</p>';
    }
echo '</div>';
