<?php
declare(strict_types=1);

namespace App\Controller\Traits\Api;

use Cake\Core\Configure;

/** @mixin \App\Controller\ApiController */
trait ApiGetSplitterTrait
{
    public function getSplitter(): void
    {
        $dir = new \DirectoryIterator(WWW_ROOT . Configure::read('AppConfig.splitterPath'));
        $prefix = 'SPLiTTER';
        $result = [];
        foreach ($dir as $fileinfo) {
            if (!$fileinfo->isDot()) {
                if (preg_match('`^'.$prefix.'(.*)\.pdf`', $fileinfo->getFilename())) {
                    $parts = explode('_', $fileinfo->getFilename());
                    $month = (int) substr($parts[1], 0, 2);
                    $year = str_replace($prefix, '', $parts[0]);
                    $name = $prefix . ' No. ' . $month . '/' . $year;
                    $result[] = [
                        'url' => Configure::read('AppConfig.serverName') . Configure::read('AppConfig.splitterPath') . '/' . $fileinfo->getFilename(),
                        'name' => $name,
                        'size' => Configure::read('AppConfig.numberHelper')->toReadableSize($fileinfo->getSize()),
                    ];
                }
            }
        }

        $this->set([
            'splitter' => $result,
        ]);

        $this->viewBuilder()->setOption('serialize', ['splitter']);

    }
}