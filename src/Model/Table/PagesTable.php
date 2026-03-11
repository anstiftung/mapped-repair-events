<?php
declare(strict_types=1);
namespace App\Model\Table;

use Cake\Validation\Validator;
use App\Model\Entity\Page;
use Cake\ORM\Behavior\TreeBehavior;
use Cake\ORM\Query\SelectQuery;

/**
 * @extends \App\Model\Table\AppRootTable<\App\Model\Entity\Page>
 */
class PagesTable extends AppRootTable
{

    public string $name_de = 'Seite';

    /**
     * @var array<int, string>
     */
    private array $flattenedArray = [];

    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->belongsTo('ParentPages', [
            'className' => 'Pages',
            'foreignKey' => 'parent_uid'
        ]);
        $this->addBehavior('Tree');
    }

    public function getPageByName(string $name): ?Page
    {
        $page = $this->find('all', conditions: [
            'Pages.name' => $name,
        ])->first();
        return $page;
    }

    public function validationAdmin(Validator $validator): Validator
    {
        $validator = parent::addUrlValidation($validator);
        $validator->notEmptyString('name', 'Bitte trage den Namen ein.');
        $validator->minLength('name', 2, 'Bitte gib einen gültigen Namen an.');
        return $validator;
    }

    /**
     * @template TSubject of array|\Cake\Datasource\EntityInterface
     * @param \Cake\ORM\Query\SelectQuery<TSubject>|array<int, \App\Model\Entity\Page> $items
     * @return array<int, string>
     */
    private function flattenNestedArrayWithChildren(SelectQuery|array $items, string $separator = ''): array
    {
        foreach ($items as $item) {
            $statusString = '';
            if (! $item->status) {
                $statusString = ' ('.__('inactive').')';
            }
            $this->flattenedArray[$item->uid] = $separator . $item->name . $statusString;
            if (! empty($item['children'])) {
                /** @var TreeBehavior $treeBehavior */
                $treeBehavior = $this->getBehavior('Tree');
                $level = $item instanceof Page ? $treeBehavior->getLevel($item) : 0;
                $this->flattenNestedArrayWithChildren(
                    $item->children,
                    str_repeat('-', $level + 1) . ' ',
                );
            }
        }

        return $this->flattenedArray;
    }

    /**
     * @param array<string|int, string|int> $conditions
     * @return \Cake\ORM\Query\SelectQuery<\App\Model\Entity\Page>
     */
    public function getThreaded(array $conditions = []): SelectQuery
    {
        $pages = $this->find('threaded',
        parentField: 'parent_uid',
        conditions: $conditions,
        order: [
            'Pages.menu_type' => 'DESC',
            'Pages.position' => 'ASC',
            'Pages.name' => 'ASC'
        ]);
        /** @var \Cake\ORM\Query\SelectQuery<\App\Model\Entity\Page> $pages */
        return $pages;
    }

    /**
     * @return array<int, string>
     */
    public function getForSelect(?int $excludePageId = null): array
    {
        $conditions = [];
        $conditions[] = 'Pages.status > ' . APP_DELETED;
        if ($excludePageId) {
            $conditions[] = 'Pages.uid <> ' . $excludePageId;
        }
        $pages = $this->getThreaded($conditions);
        $flattenedPages = $this->flattenNestedArrayWithChildren($pages);
        return $flattenedPages;
    }

}
?>