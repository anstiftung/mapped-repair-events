<?php
declare(strict_types=1);

echo $object->out_of_quota_count === 0 ? '' : h((string)$object->out_of_quota_count);