<?php
declare(strict_types=1);

    if (!empty($categoriesForView)) {
        echo '<div class="categories-wrapper">';
        foreach($categoriesForView as $key => $category) {
           echo '<div class="category-wrapper">';
              if (!empty($category)) {
                echo '<b>'.$categories[$key].'</b>';
                echo '<ul><li>'.join(', ', $category).'</li></ul>';
              }
          echo '</div>';
        }
       echo '</div>';
     }

?>