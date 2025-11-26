<?php
declare(strict_types=1);

if (!empty($_SERVER['REQUEST_URI'])) {
    echo '<b>RequestUri</b>:' .$_SERVER['REQUEST_URI'].'<br /><br />';
}

if (!empty($_SERVER['HTTP_REFERER'])) {
    echo '<b>Referer</b>:' .$_SERVER['HTTP_REFERER'].'<br /><br />';
}

echo '<b>Message</b><br />';
echo '<pre>';
    print_r($message); // @phpstan-ignore-line
echo '</pre>';
echo '<br /><br />';

echo '<b>User</b><br />';
echo '<pre>';
    print_r($identity); // @phpstan-ignore-line
echo '</pre>';
echo '<br /><br />';

echo '<b>Request</b><br />';
echo '<pre>';
    print_r($_REQUEST); // @phpstan-ignore-line
echo '</pre>';
echo '<br /><br />';

echo '<b>Server</b><br />';
echo '<pre>';
    print_r($_SERVER); // @phpstan-ignore-line
echo '</pre>';
echo '<br /><br />';

echo '<b>Files</b><br />';
echo '<pre>';
    print_r($_FILES); // @phpstan-ignore-line
echo '</pre>';
echo '<br /><br />';


?>