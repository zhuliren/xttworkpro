<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/6/2
 * Time: 12:57
 */

namespace app\index\model;


class BasicOperation
{
    function my_sort($arrays, $sort_key, $sort_order = SORT_DESC)
    {
        if (is_array($arrays)) {
            foreach ($arrays as $array) {
                if (is_array($array)) {
                    $key_arrays[] = $array[$sort_key];
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
        array_multisort($key_arrays, $sort_order, $arrays);
        return $arrays;
    }
}