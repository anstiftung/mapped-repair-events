<?php
  echo $this->element('highlightNavi', ['main' => 'Posts']);
  $heading = null;
  $blogUrl = '';
  if (isset($this->request->getQuery()['val-opt-2']) && isset($blogs[$this->request->getQuery()['val-opt-2']])) {
      $heading = $blogs[$this->request->getQuery()['val-opt-2']];
      $blogUrl = $this->request->getQuery()['val-opt-2'];
  }
  
    echo $this->element('list'
        ,[
       'objects' => $objects
      ,'heading' => $heading
      ,'newMethod' => ['url' => 'urlPostNew', 'param' => $blogUrl]
      ,'editMethod' => ['url' => 'urlPostEdit']
      ,'showMethod' => ['url' => 'urlPostDetail']
      ,'optionalSearchForms' => [
         ['options' => $users, 'value' => 'Posts.owner', 'label' => 'Owner'],
         ['options' => $blogs, 'value' => 'Posts.blog_id', 'label' => 'Blog']
      ]
    ,'fields' => [
         ['name' => 'uid', 'label' => 'UID']
        ,['name' => 'image', 'label' => 'Bild']
        ,['name' => 'name', 'label' => 'Titel']
        ,['name' => 'blog.name', 'label' => 'Blog']
        ,['name' => 'city', 'label' => 'Ort']
        ,['name' => 'publish', 'type' => 'datetime', 'label' => 'veröffentlicht ab']
        ,['name' => 'author', 'label' => 'Autor']
        ,['name' => 'owner_user.name', 'label' => 'Owner']
        ,['name' => 'created', 'type' => 'datetime', 'label' => 'erstellt']
        ,['name' => 'updated', 'type' => 'datetime', 'label' => 'geändert']
        ]
        ]
    );
?>