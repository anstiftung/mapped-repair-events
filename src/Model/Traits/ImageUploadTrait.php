<?php
declare(strict_types=1);

namespace App\Model\Traits;

use ArrayObject;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;

/**
 * for tables with single image upload (field "image", files named by uid in files/uploadify/{table})
 */
trait ImageUploadTrait {

    /**
     * removed image files are deleted after the (outermost) transaction is committed
     * @param \Cake\Event\EventInterface<\Cake\Datasource\EntityInterface> $event
     * @param ArrayObject<string, mixed> $options
     */
    public function afterSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        $originalImage = (string) $entity->getOriginal('image');
        if (!$entity->isDirty('image') || !empty($entity->get('image')) || $originalImage === '') {
            return;
        }
        $uid = (int) $entity->get('uid');
        $this->getConnection()->afterCommit(function () use ($originalImage, $uid): void {
            $this->deleteImage($originalImage, $uid);
        });
    }

    /**
     * every object has its own image file named by its uid (e.g. "123.jpg?1700000000")
     */
    public function getImageForUid(string $image, int $uid): string
    {
        $extension = pathinfo(explode('?', $image)[0], PATHINFO_EXTENSION);
        return $uid . '.' . $extension . '?' . time();
    }

    /**
     * absolute paths of original image and all thumbs
     * @return list<string>
     */
    public function getImageFiles(string $image): array
    {
        $htmlHelper = Configure::read('AppConfig.htmlHelper');
        $fileName = explode('?', $image)[0];
        $methods = ['getOriginalImage'];
        foreach (array_keys(Configure::read('AppConfig.thumbSizes')) as $thumbSize) {
            $methods[] = 'getThumbs' . $thumbSize . 'Image';
        }
        $files = [];
        foreach ($methods as $method) {
            $files[] = WWW_ROOT . ltrim($htmlHelper->$method($fileName, $this->getTable()), '/');
        }
        return $files;
    }

    /**
     * gives the object its own copy of the image if it references the image file of another object (e.g. duplicated events)
     */
    public function copyForeignImage(EntityInterface $entity): void
    {
        $image = (string) $entity->get('image');
        $uid = (int) $entity->get('uid');
        if ($image === '' || str_starts_with($image, $uid . '.')) {
            return;
        }
        $ownImage = $this->getImageForUid($image, $uid);
        $this->copyImage($image, $ownImage);
        $this->updateAll(['image' => $ownImage], ['uid' => $uid]);
        $entity->set('image', $ownImage);
        $entity->setDirty('image', false);
    }

    /**
     * copies original image and all thumbs
     */
    public function copyImage(string $sourceImage, string $targetImage): void
    {
        $targetFiles = $this->getImageFiles($targetImage);
        foreach ($this->getImageFiles($sourceImage) as $index => $sourceFile) {
            if (is_file($sourceFile)) {
                copy($sourceFile, $targetFiles[$index]);
            }
        }
    }

    /**
     * deletes original image and all thumbs - only if the image file belongs to the object (named by its uid)
     */
    public function deleteImage(string $image, int $uid): void
    {
        if (!str_starts_with($image, $uid . '.')) {
            return;
        }
        foreach ($this->getImageFiles($image) as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

}
