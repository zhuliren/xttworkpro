<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/9
 * Time: 10:36
 */

namespace app\index\model;


class BasicData
{
    public function getAppId()
    {
        $appid = 'wxf558228c1d63b971';
        return $appid;
    }

    public function getAppSecret(){
        $secret= '0deca3dbfcbff4ca3c5142866b94725b';
        return $secret;
    }
}