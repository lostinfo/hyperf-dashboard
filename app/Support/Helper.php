<?php

declare(strict_types=1);

namespace App\Support;


class Helper
{
    public static function filterMenusByPaths(array $menus, array $paths): array
    {
        foreach ($menus as $menu_group_index => $menu_group) {
            foreach ($menu_group['menus'] as $menu_index => $menu) {
                if ($menu['unfolded']) {
                    foreach ($menu['children'] as $menu_item_index => $menu_item) {
                        if (!in_array($menu_item['path'], $paths)) {
                            unset($menus[$menu_group_index]['menus'][$menu_index]['children'][$menu_item_index]);
                        }
                    }
                    if (count($menus[$menu_group_index]['menus'][$menu_index]['children']) < 1) {
                        unset($menus[$menu_group_index]['menus'][$menu_index]);
                    }
                } else {
                    if (!in_array($menu['path'], $paths)) {
                        unset($menus[$menu_group_index]['menus'][$menu_index]);
                    }
                }
            }
            if (count($menus[$menu_group_index]['menus']) < 1) {
                unset($menus[$menu_group_index]);
            }
        }
        return array_values($menus);
    }
}
