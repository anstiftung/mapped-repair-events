<?php
declare(strict_types=1);
namespace App\Model\Traits;

trait ApproveMultipleTrait
{

    /**
     * @param array<int|string> $ids
     */
    public function setApprovedMultiple(array $ids): false|int
    {
        if (empty($ids)) {
            return false;
        }

        $primaryKey = $this->getPrimaryKey();
        if (is_array($primaryKey)) {
            $primaryKey = $primaryKey[0] ?? null;
        }
        if (!is_string($primaryKey) || $primaryKey === '') {
            return false;
        }

        $affectedCount = $this->updateAll(
            [
                'status' => APP_ON,
                'modified' => date('Y-m-d H:i:s'),
            ],
            [
                'status <' => APP_ON,
                $primaryKey . ' IN' => $ids,
            ]
        );

        return $affectedCount;
    }
}
