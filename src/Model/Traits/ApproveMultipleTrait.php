<?php
declare(strict_types=1);
namespace App\Model\Traits;

trait ApproveMultipleTrait
{
    public function setApprovedMultiple(array $ids): false|int
    {
        if (empty($ids)) {
            return false;
        }

        $affectedCount = $this->updateAll(
            [
                'status' => APP_ON,
                'modified' => date('Y-m-d H:i:s'),
            ],
            [
                'status <' => APP_ON,
                'id IN' => $ids,
            ]
        );

        return $affectedCount;
    }
}
