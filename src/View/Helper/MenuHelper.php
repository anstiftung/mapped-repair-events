<?php

namespace App\View\Helper;

use Cake\Core\Configure;
use Cake\View\Helper;

class MenuHelper extends Helper
{

    public function render($array, $options)
    {
        $tmpMenu = '<ul id="'.$options['id'].'" class="'.$options['class'].'">';
        if (!empty($options['header'])) {
            $tmpMenu .= '<li class="header">'.$options['header'].'</li>';
        }
        foreach ($array as $index => $item) {
            $tmpMenu .= $this->buildMenuItem($item, $index);
        }
        if (!empty($options['footer'])) {
            $tmpMenu .= '<li class="footer">'.$options['footer'].'</li>';
        }
        $tmpMenu .= '</ul>';
        return $tmpMenu;
    }

    public function buildPageMenu($pages)
    {

        $menu = [];
        foreach ($pages as $page) {
            $children = [];
            if (!empty($page->children)) {
                foreach ($page->children as $childPage) {
                    if ($childPage->extern_url != '') {
                        $slug = $childPage->extern_url;
                    } else {
                        $slug = Configure::read('AppConfig.htmlHelper')->urlPageDetail($childPage->url);
                    }
                    $children[] = [
                        'name' => $childPage->name,
                        'slug' => $slug
                    ];
                }
            }
            if ($page->extern_url != '') {
                $slug = $page->extern_url;
            } else {
                $slug = Configure::read('AppConfig.htmlHelper')->urlPageDetail($page->url);
            }
            $menu[] = [
                'name' => $page->name,
                'slug' => $slug,
                'children' => $children
            ];
        }
        return $menu;
    }

    private function buildMenuItem($item, $index)
    {

        $liClass = [];
        if (!empty($item['children'])) {
            $liClass[] = 'has-children';
            $liClass[] = 'has-icon';
        }
        $tmpMenuItem = '<li' . (!empty($liClass) ? ' class="' . join(' ', $liClass).'"' : '').'>';

            $tmpMenuItem .= $this->renderMenuElement(
                $item['slug'],
                $item['name'],
                $item['options']['style'] ?? '',
                $item['options']['class'] ?? [],
                $item['options']['fa-icon'] ?? ''
            );

        if (!empty($item['children'])) {
            $tmpMenuItem .= '<ul class="submenu">';
            foreach ($item['children'] as $index => $child) {
                $tmpMenuItem .= $this->buildMenuItem($child, $index);
            }
            $tmpMenuItem .= '</ul>';
        }

        $tmpMenuItem .= '</li>';

        return $tmpMenuItem;
    }

    private function renderMenuElement($slug, $name, $style = '', $class = [], $fontAwesomeIconClass = '')
    {

        if ($style != '') {
            $style = ' style="'.$style.'"';
        }
        if ($slug != '/' && isset($_SERVER['REQUEST_URI']) && preg_match('`' . preg_quote($slug) . '`', $_SERVER['REQUEST_URI'])) {
            $applyActiveClass = true;

            if ($applyActiveClass) {
                $class[] = 'active';
            }
        }

        if ($slug == '/' && isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] == '/') {
            $class[] = 'active';
        }

        if ($fontAwesomeIconClass != '') {
            $class[] = 'has-icon';
        }
//         $fontAwesomeIconString = '<i class="fas '.@$fontAwesomeIconClass.'"></i>';
        $fontAwesomeIconString = '';

        $classString = '';
        if (!empty($class)) {
            $classString = ' class="' . join(' ', $class). '" ';
        }

        $naviElement = '<a' . $classString . $style.' href="'.$slug.'" title="'.h(strip_tags($name)).'">'.$fontAwesomeIconString.$name.'</a>';

        return $naviElement;
    }

}
