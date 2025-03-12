<?php
declare(strict_types=1);
namespace App\Model\Traits;

trait ApproveMultipleTrait
{
    public function setApprovedMultiple(array $ids): void
    {
        if (empty($ids)) {
            return;
        }
        $this->updateAll(
            ['status' => APP_ON],
            ['id IN' => $ids]
        );
    }
}
