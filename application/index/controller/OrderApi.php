<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/29
 * Time: 12:41
 */

namespace app\index\controller;


class OrderApi
{
    public function getOrder()
    {
        $acc = $_REQUEST['acc'];
        $update = $_REQUEST['update'];
        $enddate = $_REQUEST['enddate'];
        $sign = $_REQUEST['sign'];
        //验证账号

    }
}