<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Traits;

use App\Model\Table\EventsTable;
use App\Model\Table\PostsTable;
use App\Model\Table\UsersTable;
use App\Model\Table\WorkshopsTable;
use App\Test\TestCase\AppTestCase;
use RuntimeException;

class ImageUploadTraitTest extends AppTestCase
{

    /** @var list<string> */
    private array $files = [];

    public function tearDown(): void
    {
        foreach ($this->files as $file) {
            @unlink($file);
        }
        parent::tearDown();
    }

    /**
     * @return list<string>
     */
    private function createImageFiles(EventsTable|WorkshopsTable|UsersTable|PostsTable $table, string $image): array
    {
        $files = $table->getImageFiles($image);
        foreach ($files as $file) {
            if (file_exists($file)) {
                $this->markTestSkipped('Existing image file must not be overwritten: ' . $file);
            }
        }
        foreach ($files as $file) {
            file_put_contents($file, 'image');
            $this->files[] = $file;
        }
        return $files;
    }

    public function testRemovedImageIsDeletedAfterSave(): void
    {
        foreach (['Workshops', 'Posts', 'Users'] as $tableName) {
            /** @var WorkshopsTable|PostsTable|UsersTable $table */
            $table = $this->getTableLocator()->get($tableName);
            $entity = $table->find()->firstOrFail();
            $uid = (int) $entity->get('uid');
            $files = $this->createImageFiles($table, $uid . '.jpg');
            $table->updateAll(['image' => $uid . '.jpg?123'], ['uid' => $uid]);

            $entity = $table->get($uid);
            $entity->set('image', '');
            $table->saveOrFail($entity, ['validate' => false]);

            $this->assertEmpty($table->get($uid)->get('image'), $tableName);
            foreach ($files as $file) {
                $this->assertFileDoesNotExist($file, $tableName);
            }
        }
    }

    public function testRemovedImageIsNotDeletedOnRollback(): void
    {
        /** @var WorkshopsTable $table */
        $table = $this->getTableLocator()->get('Workshops');
        $entity = $table->find()->firstOrFail();
        $uid = (int) $entity->get('uid');
        $files = $this->createImageFiles($table, $uid . '.jpg');
        $table->updateAll(['image' => $uid . '.jpg?123'], ['uid' => $uid]);

        try {
            $table->getConnection()->transactional(function () use ($table, $uid): void {
                $entity = $table->get($uid);
                $entity->set('image', '');
                $table->saveOrFail($entity, ['validate' => false]);
                throw new RuntimeException('rollback');
            });
        } catch (RuntimeException) {
        }

        $this->assertSame($uid . '.jpg?123', $table->get($uid)->get('image'));
        foreach ($files as $file) {
            $this->assertFileExists($file);
        }
    }

    public function testImageOfOtherObjectIsNotDeleted(): void
    {
        /** @var WorkshopsTable $table */
        $table = $this->getTableLocator()->get('Workshops');
        $entities = $table->find()->limit(2)->all()->toList();
        $otherUid = (int) $entities[1]->get('uid');
        $files = $this->createImageFiles($table, $otherUid . '.jpg');
        $uid = (int) $entities[0]->get('uid');
        $table->updateAll(['image' => $otherUid . '.jpg?123'], ['uid' => $uid]);

        $entity = $table->get($uid);
        $entity->set('image', '');
        $table->saveOrFail($entity, ['validate' => false]);

        foreach ($files as $file) {
            $this->assertFileExists($file);
        }
    }

}
