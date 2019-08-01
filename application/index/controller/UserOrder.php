<?php


namespace app\index\controller;


use think\Db;

class UserOrder
{
    public function creatOrder()
    {
        $user_id = $_REQUEST['userid'];
        $acc = $_REQUEST['acc'];
        $name = $_REQUEST['name'];
        $phone = $_REQUEST['phone'];
        $address = $_REQUEST['address'];
        $pwd = md5(md5($_REQUEST['pwd']));
        //查询卡券
        $carddata = Db::table('card')->where('acc', $acc)->find();
        if ($carddata) {
            if ($pwd == $carddata['pwd']) {
                if ($carddata['type'] == 2) {
                    //修改卡券状态
                    Db::table('card')->where('acc', $acc)->update(['type' => 3, 'user_id' => $user_id, 'used_time' => date("Y-m-d H:i:s", time())]);
                    //插入用户订单表
                    Db::table('user_order')->insert(['user_id' => $user_id, 'user_id' => $user_id, 'card_acc' => $acc, 'name' => $name,
                        'phone' => $phone, 'address' => $address, 'creat_time' => date("Y-m-d H:i:s", time())]);
                    $data = array('status' => 0, 'msg' => '成功', 'data' => '');
                    //将用户归入该代理商渠道下
                    $userdata = Db::table('user')->where('id', $user_id)->find();
                    if ($userdata) {
                        if (!$userdata['distributor_id']) {
                            $did = $carddata['did'];
                            Db::table('user')->where('id', $user_id)->update(['distributor_id' => $did]);
                        }
                    }
                } else {
                    $data = array('status' => 1, 'msg' => '卡券无法使用', 'data' => '');
                }
            } else {
                $data = array('status' => 1, 'msg' => '卡券密码错误', 'data' => '');
            }
        } else {
            $data = array('status' => 1, 'msg' => '卡券账号不存在', 'data' => '');
        }
        return json($data);
    }

    public function myOrderList()
    {
        $user_id = $_REQUEST['userid'];
        $userorderdata = Db::view('user_order', 'name,phone,address')
            ->view('card', 'acc,used_time', 'user_order.card_id=card.acc', 'LEFT')
            ->view('goods_size', 'size,card_price', 'card.gsid=goods_size.id', 'LEFT')
            ->view('goods', 'name as goodsname,headimg', 'goods_size.goods_id=goods.id', 'LEFT')
            ->where('user_order.user_id', $user_id)
            ->select();
        if ($userorderdata) {
            $data = array('status' => 0, 'msg' => '成功', 'data' => $userorderdata);
        } else {
            $data = array('status' => 1, 'msg' => '无订单', 'data' => '');
        }
        return json($data);
    }

    public function userOrderList()
    {
        $starttime = $_REQUEST['starttime'];
        $endtime = $_REQUEST['endtime'];
        $type = $_REQUEST['type'];//0未发货 1已发货
        $orderdata = Db::view('user_order', 'user_id,card_acc,name,phone,address,creat_time')
            ->view('card', 'gsid,did', 'user_order.card_acc=card.acc', 'LEFT')
            ->view('goods_size', 'size as sizename', 'card.gsid=goods_size.id', 'LEFT')
            ->where('creat_time', '>=', $starttime)
            ->where('creat_time', '<=', $endtime)
            ->where('user_order.type', $type)
            ->order('user_order.creat_time asc')
            ->select();
        $data = array('status' => 0, 'msg' => '成功', 'data' => $orderdata);
        return json($data);
    }

    public function userOrderComplete()
    {
        $rid = $_REQUEST['rid'];
        $card_acc = $_REQUEST['card_acc'];
        $express = $_REQUEST['express'];
        $express_np = $_REQUEST['express_np'];
        $orderdata = Db::table('user_order')->where('card_acc', $card_acc)->find();
        if ($orderdata) {
            if ($orderdata['type'] == 0) {
                Db::table('user_order')->where('card_acc', $card_acc)->update(['type' => 1, 'express' => $express, 'express_np' => $express_np]);
                $data = array('status' => 0, 'msg' => '成功', 'data' => '');
            } else {
                $data = array('status' => 1, 'msg' => '订单错误', 'data' => '');
            }
        } else {
            $data = array('status' => 1, 'msg' => '订单错误', 'data' => '');
        }
        return json($data);
    }
}