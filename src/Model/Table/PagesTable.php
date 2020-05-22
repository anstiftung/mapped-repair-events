<?php
namespace App\Model\Table;

use Cake\Validation\Validator;

class PagesTable extends AppTable
{

    public $name_de = 'Seite';

    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->belongsTo('ParentPages', [
            'className' => 'Pages',
            'foreignKey' => 'parent_uid'
        ]);
        $this->addBehavior('Tree');
    }

    public function getPageByName($name)
    {
        $page = $this->find('all', [
            'conditions' => [
                'Pages.name' => $name
            ]
        ])->first();
        return $page;
    }

    public function validationAdmin(Validator $validator)
    {
        $validator = parent::addUrlValidation($validator);
        $validator->notEmptyString('name', 'Bitte trage den Namen ein.');
        $validator->minLength('name', 2, 'Bitte gib einen gültigen Namen an.');
        return $validator;
    }

    private $flattenedArray = [];

    private function flattenNestedArrayWithChildren($array, $separator = '')
    {
        foreach ($array as $item) {
            $statusString = '';
            if (! $item->status) {
                $statusString = ' ('.__('offline').')';
            }
            $this->flattenedArray[$item->uid] = $separator . $item->name . $statusString;
            if (! empty($item['children'])) {
                $this->flattenNestedArrayWithChildren($item->children, str_repeat('-', $this->getLevel($item) + 1) . ' ');
            }
        }

        return $this->flattenedArray;
    }

    public function getThreaded($conditions = [])
    {
        $pages = $this->find('threaded', [
            'parentField' => 'parent_uid',
            'conditions' => $conditions,
            'order' => [
                'Pages.menu_type' => 'DESC',
                'Pages.position' => 'ASC',
                'Pages.name' => 'ASC'
            ]
        ]);
        return $pages;
    }

    public function getForSelect($excludePageId = null)
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