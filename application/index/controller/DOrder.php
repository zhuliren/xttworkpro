<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/18
 * Time: 11:33
 */

namespace app\index\controller;


use think\Db;

class DOrder
{
    public function creatDOrder()
    {
        $did = $_REQUEST['did'];
        $pay_type = $_REQUEST['paytype'];//支付方式  0、现金支付 1、授信支付
        $iscard = $_REQUEST['iscard'];//0、卡券1、现货
        //判断订单类型 若为门店并使用现金支付则需要区域确认，其他情况均为财务确认
        $ddata = Db::table('distributor')->where('id', $did)->find();
        if ($ddata) {
            if ($ddata['type'] == 2 && $pay_type == 0) {
                $ordertype = 1;
            } else {
                $ordertype = 2;
            }
            $lc = $ddata['lc'];
            $usedlc = $ddata['usedlc'];
            $uselc = $lc - $usedlc;
            $payprice = 0;
            //生成订单id
            $order_id = $did . date('Ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
            //查询购物车勾选商品
            $shopcardata = Db::table('shopcar')->where('did', $did)->where('ischoose', 1)->column('id,goods_size_id,num');
            foreach ($shopcardata as $item) {
                $shopcarid = $item['id'];
                $goods_size_id = $item['goods_size_id'];
                $num = $item['num'];
                //计算商品金额
                $goodssizedata = Db::table('goods_size')->where('id', $goods_size_id)->find();
                if ($goodssizedata) {
                    $cost = $goodssizedata['cost'];
                }
                $payprice += ($cost * $num);
                //删除当前购物车物品插入到订单详情中
                Db::table('shopcar')->where('id', $shopcarid)->delete();
                Db::table('order_details')
                    ->insert(['order_id' => $order_id, 'goods_size_id' => $goods_size_id, 'goods_num' => $num]);
            }
            //确认授信额度
            if ($pay_type == 1 && $uselc < $payprice) {
                $data = array('status' => 1, 'msg' => '授信额度不足', 'data' => '');
            } else {
                //授信支付扣除账户中的授信额度
                if ($pay_type == 1) {
                    $newusedlc = $usedlc + $payprice;
                    //记录到授信使用表中
                    Db::table('lc_history')->insert(['did' => $did, 'amount' => $payprice, 'type' => 0, 'creattime' => date("Y-m-d H:i:s", time())]);
                    //更新账户中的授信
                    Db::table('distributor')->where('id', $did)->update(['usedlc' => $newusedlc]);
                }
                //创建订单
                Db::table('order')
                    ->insert(['order_id' => $order_id, 'did' => $did, 'creat_time' => date("Y-m-d H:i:s", time()), 'paytype' => $pay_type, 'ordertype' => $ordertype, 'payprice' => $payprice, 'iscard' => $iscard]);
                $returndata = array('order_id' => $order_id);
                $data = array('status' => 0, 'msg' => '成功', 'data' => $returndata);
            }
        } else {
            $data = array('status' => 1, 'msg' => '代理商id错误', 'data' => '');
        }
        return json($data);
    }

    public function myShopOrderList()
    {
        $did = $_REQUEST['did'];
        $ordertype = $_REQUEST['type'];//门店订单状态 1、区域确认中 2、财务确认中
        $myshoporderlist = array();
        $channerdata = Db::table('channer')->where('region_id', $did)->column('shop_id');
        foreach ($channerdata as $item) {
            $shop_id = $item;
            $orderdata = Db::table('order')->where('did', $shop_id)->where('ordertype', $ordertype)->column('order_id,creat_time,paytype,ordertype,payprice,iscard');
            if ($orderdata) {
                $myshoporderlist[] = $orderdata;
            }
        }
        if ($myshoporderlist) {
            $data = array('status' => 0, 'msg' => '成功', 'data' => $myshoporderlist);
        } else {
            $data = array('status' => 1, 'msg' => '无订单', 'data' => '');
        }
        return json($data);
    }

    public function dOrderDetails()
    {
        $order_id = $_REQUEST['orderid'];
        $orderdata = Db::view('order','order_id,creat_time,paytype,ordertype,payprice,iscard')
            ->view('distributor','name,phone,address','order.did=distributor.id')
            ->where('order.order_id', $order_id)
            ->select();
        if ($orderdata) {
            $orderdetailsdata = Db::view('order_details', 'goods_num')
                ->view('goods_size', 'size,cost,price,card_price', 'order_details.goods_size_id=goods_size.id', 'LEFT')
                ->view('goods', 'name,headimg', 'goods_size.goods_id=goods.id', 'LEFT')
                ->where('order_details.order_id', $order_id)
                ->select();
            $returndata = array('orderdata' => $orderdata, 'orderdetailsdata' => $orderdetailsdata);
            $data = array('status' => 0, 'msg' => '成功', 'data' => $returndata);
        } else {
            $data = array('status' => 1, 'msg' => '订单id错误', 'data' => '');
        }
        return json($data);
    }

    public function dConfirmOrder()
    {
        $did = $_REQUEST['did'];
        $order_id_list_string = $_REQUEST['orderidlist'];
        $order_id_list = explode(",", $order_id_list_string);
        foreach ($order_id_list as $order_iditem) {
            $orderdata = Db::table('order')->where('order_id', $order_iditem)->find();
            if ($orderdata) {
                $shop_id = $orderdata['did'];
                $channerdata = Db::table('channer')->where('region_id', $did)->where('shop_id', $shop_id)->find();
                if ($channerdata) {
                    Db::table('order')->where('order_id', $order_iditem)->update(['ordertype' => 2]);
                }
            }
        }
        $data = array('status' => 0, 'msg' => '成功', 'data' => '');
        return json($data);
    }

    public function myOrderList()
    {
        $did = $_REQUEST['did'];
        $orderlistdata = Db::table('order')->where('did', $did)->column('order_id,creat_time,paytype,ordertype,payprice,iscard');
        if ($orderlistdata) {
            $data = array('status' => 0, 'msg' => '成功', 'data' => $orderlistdata);
        } else {
            $data = array('status' => 1, 'msg' => '暂无订单', 'data' => '');
        }
        return json($data);
    }
}