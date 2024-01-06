<?php
namespace App\Model\Table;

use Cake\Validation\Validator;

class PostsTable extends AppTable
{

    public $name_de = 'Post';

    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->belongsTo('Blogs');
        $this->hasMany('Photos', [
            'foreignKey' => 'object_uid',
            'conditions' => [
                'Photos.status' => APP_ON
            ],
            'sort' => [
                'Photos.rank' => 'ASC',
                'Photos.text' => 'ASC'
            ]
        ]);
    }

    public function validationDefault(Validator $validator): \Cake\Validation\Validator
    {
        $validator = $this->validationAdmin($validator);
        $validator->minLength('city', 2, 'Bitte trage einen Ort ein.');
        $validator->minLength('city', 1, 'Bitte gib einen Author an.');
        return $validator;
    }

    public function validationAdmin(Validator $validator)
    {
        $validator = parent::addUrlValidation($validator);
        $validator->notEmptyString('name', 'Bitte gib einen Titel an.');
        $validator->minLength('name', 2, 'Bitte gib einen gültigen Titel an.');
        $validator->notEmptyString('publish', 'Bitte trage ein, ab wann der Post gezeigt werden soll.');
        return $validator;
    }

    public function getLatestPosts() {
        $posts = $this->find('all',
        fields: [
            'Posts.uid',
            'Posts.name',
            'Posts.text',
            'Posts.url',
            'Posts.image',
            'Posts.image_alt_text',
        ],
        limit: 3,
        order: ['Posts.publish' => 'DESC'],
        conditions: [
            'Posts.status' => APP_ON,
            'Posts.image != ' => ''
        ]);
        return $posts;
    }

}
?>