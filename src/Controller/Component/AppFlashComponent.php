<?php
declare(strict_types=1);
namespace App\Controller\Component;

use Cake\Controller\Component\FlashComponent;

class AppFlashComponent extends FlashComponent
{

    public function setFlashMessage(string $message): void
    {
        $this->set($message, [
            'element' => 'default',
            'params' => [
                'class' => 'success',
            ],
        ]);
    }

    public function setFlashError(string $message): void
    {
        $this->set($message, [
            'element' => 'default',
            'params' => [
                'class' => 'error',
            ],
        ]);
    }
}

?>