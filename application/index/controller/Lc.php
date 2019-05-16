<?php


namespace app\index\controller;


use think\Db;

class Lc
{
    public function myLc()
    {
        $did = $_REQUEST['did'];
        $sqldata = Db::table('distributor')->where('id',$did)->find();

    }

    public function lcHis()
    {

    }

    public function reLc()
    {

    }

    public function changeLc()
    {

    }
}