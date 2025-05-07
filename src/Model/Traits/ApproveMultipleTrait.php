<?php
declare(strict_types=1);
namespace App\Model\Traits;

trait ApproveMultipleTrait
{

    /**
     * @param array<int> $ids
     */
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
                $this->getPrimaryKey() . ' IN' => $ids,
            ]
        );

        return $affectedCount;
    }
}
