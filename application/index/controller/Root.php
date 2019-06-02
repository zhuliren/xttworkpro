<?php


namespace app\index\controller;


use app\index\model\BasicOperation;
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
        $sort_key = $_REQUEST['sort_key'];
        $operation = new BasicOperation();
        $userdata = Db::table('user')->column('id,name,headimg,creattime');
        if ($userdata) {
            //重组数据
            $returndata = array();
            foreach ($userdata as $item) {
                $ordernum = Db::table('user_order')->where('user_id', $item['id'])->count('id');
                $returndata[] = array('id' => $item['id'], 'name' => $item['name'], 'headimg' => $item['headimg'], 'creattime' => $item['creattime'], 'ordernum' => $ordernum);
            }
            $returndata = $operation->my_sort($returndata, $sort_key, SORT_DESC);
            $data = array('status' => 0, 'msg' => '成功', 'data' => $returndata);
        } else {
            $data = array('status' => 1, 'msg' => '无用户', 'data' => '');
        }
        return json($data);
    }

    public function dList()
    {
        $sort_key = $_REQUEST['sort_key'];
        $operation = new BasicOperation();
        $ddata = Db::table('distributor')->column('id,account,wxid,address,type,grade,name,phone,due,lc,usedlc');
        if ($ddata) {
            $returndata = $operation->my_sort($ddata, $sort_key, SORT_DESC);
            $data = array('status' => 0, 'msg' => '成功', 'data' => $returndata);
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

    public function allOrderList()
    {
        $startdate = $_REQUEST['startdate'];
        $enddate = $_REQUEST['enddate'];
        $type = $_REQUEST['type'];//0.全部 1.卡券 2.现货 3.用户提货
        $sort_key = $_REQUEST['sort_key'];
        $limit = $_REQUEST['limit'];
        $page = $_REQUEST['page'];
        $start = $page * $limit;
        $operation = new BasicOperation();
        //判断查看类型
        if ($type == 0) {
            $datanum = Db::table('order')->where('creat_time', 'between time', [$startdate, $enddate])->count();
            $returndata = Db::table('order')->where('creat_time', 'between time', [$startdate, $enddate])
                ->limit($start, $limit)
                ->column('order_id,did,creat_time,paytype,ordertype,payprice,iscard');
            if ($returndata) {
                $returndata = $operation->my_sort($returndata, $sort_key, SORT_DESC);
                $alldata = array('list' => $returndata, 'num' => $datanum);
                $data = array('status' => 0, 'msg' => '成功', 'data' => $alldata);
            } else {
                $data = array('status' => 1, 'msg' => '无订单', 'data' => '');
            }
        } else if ($type == 1) {
            $datanum = Db::table('order')->where('creat_time', 'between time', [$startdate, $enddate])->where('iscard', 0)->count();
            $returndata = Db::table('order')->where('creat_time', 'between time', [$startdate, $enddate])
                ->where('iscard', 0)
                ->limit($start, $limit)
                ->column('order_id,did,creat_time,paytype,ordertype,payprice,iscard');
            if ($returndata) {
                $returndata = $operation->my_sort($returndata, $sort_key, SORT_DESC);
                $alldata = array('list' => $returndata, 'num' => $datanum);
                $data = array('status' => 0, 'msg' => '成功', 'data' => $alldata);
            } else {
                $data = array('status' => 1, 'msg' => '无订单', 'data' => '');
            }
        } else if ($type == 2) {
            $datanum = Db::table('order')->where('creat_time', 'between time', [$startdate, $enddate])->where('iscard', 1)->count();
            $returndata = Db::table('order')->where('creat_time', 'between time', [$startdate, $enddate])
                ->where('iscard', 1)
                ->limit($start, $limit)
                ->column('order_id,did,creat_time,paytype,ordertype,payprice,iscard');
            if ($returndata) {
                $returndata = $operation->my_sort($returndata, $sort_key, SORT_DESC);
                $alldata = array('list' => $returndata, 'num' => $datanum);
                $data = array('status' => 0, 'msg' => '成功', 'data' => $alldata);
            } else {
                $data = array('status' => 1, 'msg' => '无订单', 'data' => '');
            }
        } else if ($type == 3) {
            $datanum = Db::table('user_order')->where('creat_time', 'between time', [$startdate, $enddate])->count();
            $returndata = Db::table('user_order')->where('creat_time', 'between time', [$startdate, $enddate])
                ->limit($start, $limit)
                ->column('user_id,card_id,name,phone,address,creat_time');
            if ($returndata) {
                $returndata = $operation->my_sort($returndata, $sort_key, SORT_DESC);
                $alldata = array('list' => $returndata, 'num' => $datanum);
                $data = array('status' => 0, 'msg' => '成功', 'data' => $alldata);
            } else {
                $data = array('status' => 1, 'msg' => '无订单', 'data' => '');
            }
        }
        return json($data);
    }
}