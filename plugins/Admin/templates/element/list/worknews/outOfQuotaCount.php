<?php
declare(strict_types=1);

$count = $object->out_of_quota_count ?? 0;
echo $count === 0 ? '' : h((string)$count);