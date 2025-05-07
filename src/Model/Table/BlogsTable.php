<?php
declare(strict_types=1);
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

    /**
     * @return array<int, string>
     */
    public function getForDropdown(): array
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