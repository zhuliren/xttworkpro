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
            //匹配菜单
            $menudata = Db::view('rootpathvk', 'roottype')
                ->view('rootpath', 'id,path,name,component,redirect,leaf,menuShow,iconCls', 'rootpathvk.pathid=rootpath.id')
                ->where('rootpathvk.roottype', $rootdata['type'])
                ->select();
            $menu = array();
            //重组数据
            foreach ($menudata as $item) {
                $zpath = Db::table('rootzpath')->where('rootpathid', $item['id'])->column('path,component,name,menuShow');
                $children = array();
                foreach ($zpath as $pathitem) {
                    $children[] = $pathitem;
                }
                $menu[] = array('path' => $item['path'], 'name' => $item['name'], 'component' => $item['component'],
                    'redirect' => $item['redirect'], 'leaf' => $item['leaf'], 'menuShow' => $item['menuShow'],
                    'iconCls' => $item['iconCls'], 'children' => $children);
            }
            $returndata = array('rid' => $rootdata['id'], 'type' => $rootdata['type'], 'menu' => $menu);
            $data = array('status' => 0, 'msg' => '成功', 'data' => $returndata);
        } else {
            $ddata = Db::table('distributor')->where('account', $acc)->where('password', $pwd)->find();
            if ($ddata) {
                $menu = array('商品库', '我的订单', '我的客户', '我的卡券');
                $returndata = array('did' => $ddata['did'], 'name' => $ddata['name'], 'account' => $ddata['account'], 'type' => $ddata['type'], 'grade' => $ddata['grade'], 'menu' => $menu);
                $data = array('status' => 10, 'msg' => '代理商登录', 'data' => $returndata);
            } else {
                $data = array('status' => 1, 'msg' => '请检查账号密码', 'data' => '');
            }
        }
        return json($data);
    }

    public function userList()
    {
        $userdata = Db::table('user')->column('id,name,headimg,creattime');
        if ($userdata) {
            $data = array('status' => 0, 'msg' => '成功', 'data' => $userdata);
        } else {
            $data = array('status' => 1, 'msg' => '无用户', 'data' => '');
        }
        return json($data);
    }

    public function dList()
    {
        $ddata = Db::table('distributor')->column('id,account,wxid,address,type,grade,name,phone,due,lc,usedlc');
        if ($ddata) {
            $data = array('status' => 0, 'msg' => '成功', 'data' => $ddata);
        } else {
            $data = array('status' => 1, 'msg' => '无代理商', 'data' => '');
        }
        return json($data);
    }

    public function rootList()
    {
        $rootdata = Db::table('root')->column('id,acc,type');
        if ($rootdata) {
            $data = array('status' => 0, 'msg' => '成功', 'data' => $rootdata);
        } else {
            $data = array('status' => 1, 'msg' => '无后台管理账号', 'data' => '');
        }
        return json($data);
    }

    public function qOrder()
    {
        $rid = $_REQUEST['rid'];
        $order_id = $_REQUEST['orderid'];
        $orderdata = Db::table('order')->where('order_id', $order_id)->find();
        if ($orderdata) {
            if ($orderdata['ordertype'] == 2) {
                Db::table('order')->where('order_id', $order_id)->update(['ordertype' => 3]);
                $data = array('status' => 0, 'msg' => '成功', 'data' => '');
            } else {
                $data = array('status' => 1, 'msg' => '订单状态错误', 'data' => '');
            }
        } else {
            $data = array('status' => 1, 'msg' => '订单号错误', 'data' => '');
        }
        return json($data);
    }
}