<?php


namespace app\index\controller;


use think\Db;

class Root
{
    public function creatRoot()
    {
        $acc = $_REQUEST['acc'];
        $pwd = md5(md5($_REQUEST['pwd']));
        $type = $_REQUEST['type'];//类型 0、超级管理员 1、运维账号 2、财务账号 3、仓管账号
        $rid = Db::table('root')->insertGetId(['acc' => $acc, 'pwd' => $pwd, 'type' => $type]);
        $data = array('status' => 0, 'msg' => '成功', 'data' => array('rid' => $rid));
        return json($data);
    }

    public function login()
    {
        $acc = $_REQUEST['acc'];
        $pwd = md5(md5($_REQUEST['pwd']));
        $rootdata = Db::table('root')->where('acc', $acc)->where('pwd', $pwd)->find();
        if ($rootdata) {
            $returndata = array('rid' => $rootdata['id'], 'type' => $rootdata['type']);
            $data = array('status' => 0, 'msg' => '成功', 'data' => $returndata);
        } else {
            $ddata = Db::table('distributor')->where('account', $acc)->where('password', $pwd)->find();
            if ($ddata) {
                $returndata = array('did' => $ddata['did'], 'name' => $ddata['name'], 'account' => $ddata['account'], 'type' => $ddata['type'], 'grade' => $ddata['grade']);
                $data = array('status' => 10, 'msg' => '代理商登录', 'data' => $returndata);
            } else {
                $data = array('status' => 1, 'msg' => '请检查账号密码', 'data' => '');
            }
        }
        return json($data);
    }

    public function userList(){
        Db::table('user')->column();
    }

    public function dList(){

    }

    public function rootList(){

    }
}