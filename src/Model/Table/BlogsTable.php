<?php
namespace App\Model\Table;

use Cake\ORM\Table;

class BlogsTable extends Table
{

    public function initialize(array $config): void
    {
        $this->hasMany('Posts', [
            'foreignKey' => 'blog_id',
            'sort' => [
                'Posts.publish' => 'DESC'
            ]
        ]);
    }

    public function getForDropdown()
    {
        $blogs = $this->find('all', order: [
            'Blogs.name' => 'ASC'
        ]);

        $preparedBlogs = [];
        foreach($blogs as $blog) {
            $preparedBlogs[$blog->id] = $blog->name;
        }
        return $preparedBlogs;
    }
}

?>