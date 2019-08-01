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
    //TODO 废接口
    public function creatNewDOrder()
    {
        $did = $_REQUEST['did'];
        $pay_type = $_REQUEST['paytype'];//支付方式  0、现金支付 1、授信支付
        $iscard = $_REQUEST['iscard'];//0、卡券1、现货
        $invoice = $_REQUEST['invoice'];//发票 0不需要1需要
        if ($invoice == 1) {
            $invoicetype = $_REQUEST['invoicetype'];//发票类型 0普票 1专票
            $invoiceinfo = $_REQUEST['invoiceinfo'];//发票信息 商品名、明细
        }
        $dremarks = $_REQUEST['dremarks'];//备注
        $ad_payment = $_REQUEST['ad_payment'];//是否使用预充值金额 0不使用 1使用
//        $subsidy = $_REQUEST['subsidy'];//是否使用装修补贴 0不使用 1使用
        $coupon = $_REQUEST['coupon'];//是否使用赠券金额 0不使用 1使用
        $name = $_REQUEST['name'];//收货人名字
        $address = $_REQUEST['address'];//收货地址
        $phone = $_REQUEST['phone'];//收货人电话
        //判断是否可以使用授信支付
        if ($pay_type == 1) {
            $lctermdata = Db::table('lcterm')->where('id', 1)->find();
            if ($lctermdata) {
                $now = date("Y-m-d", time());
                if (!($now > $lctermdata['lcstart'] && $now < $lctermdata['lcend'])) {
                    $data = array('status' => 1, 'msg' => '目前无法使用授信支付', 'data' => '');
                    return json($data);
                }
            }
        }
        //判断订单类型 若为门店并使用现金支付则需要区域确认，其他情况均为财务确认
        $ddata = Db::table('distributor')->where('id', $did)->find();
        if ($ddata) {
            $discount = $ddata['discount'];
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
            $sumnum = 0;
            foreach ($shopcardata as $item) {
                $shopcarid = $item['id'];
                $goods_size_id = $item['goods_size_id'];
                $num = $item['num'];
                $sumnum += $num;
                //计算商品金额
                $goodssizedata = Db::table('goods_size')->where('id', $goods_size_id)->find();
                if ($goodssizedata) {
                    $cost = $goodssizedata['price'] * $discount;
                }
                $payprice += ($cost * $num);
                //删除当前购物车物品插入到订单详情中
                Db::table('shopcar')->where('id', $shopcarid)->delete();
                Db::table('order_details')
                    ->insert(['order_id' => $order_id, 'goods_size_id' => $goods_size_id, 'goods_num' => $num]);
            }
            if ($payprice <= 0) {
                $data = array('status' => 1, 'msg' => '订单金额为0无法下单', 'data' => '');
                return json($data);
            }
            $sumprice = $payprice;
            //查询用户钱包余额
            $walletdata = Db::table('wallet')->where('did', $did)->find();
            if ($walletdata) {
//                //按照扣款循序进行扣款 补贴-》赠券-》预充值
//                if ($subsidy == 1 && $payprice > 0) {
//                    $subsidysum = $walletdata['subsidy'];
//                    if ($subsidysum <= $payprice) {
//                        $sy = 0;
//                        $kc = $subsidysum;
//                        $payprice -= $subsidysum;
//                    } else {
//                        $sy = $subsidysum - $payprice;
//                        $kc = $payprice;
//                        $payprice = 0;
//                    }
//                    //插入数据库
//                    Db::table('wallet')->where('did', $did)->update(['subsidy' => $sy]);
//                    Db::table('wallet_detailed')->insert(['did' => $did, 'amount' => $kc, 'type' => 2, 'time' => date("Y-m-d H:i:s", time())]);
//                }
                if ($coupon == 1 && $payprice > 0) {
                    $couponsum = $walletdata['coupon'];
                    if ($couponsum > 0) {
                        if ($couponsum <= $payprice) {
                            $sy = 0;
                            $kc = $couponsum;
                            $payprice -= $couponsum;
                        } else {
                            $sy = $couponsum - $payprice;
                            $kc = $payprice;
                            $payprice = 0;
                        }
                        //插入数据库
                        Db::table('wallet')->where('did', $did)->update(['coupon' => $sy]);
                        Db::table('wallet_detailed')->insert(['did' => $did, 'amount' => '-' . $kc, 'type' => 3, 'time' => date("Y-m-d H:i:s", time())]);
                    }
                }
                if ($ad_payment == 1 && $payprice > 0) {
                    $ad_paymentsum = $walletdata['ad_payment'];
                    if ($ad_paymentsum > 0) {
                        if ($ad_paymentsum <= $payprice) {
                            $sy = 0;
                            $kc = $ad_paymentsum;
                            $payprice -= $ad_paymentsum;
                        } else {
                            $sy = $ad_paymentsum - $payprice;
                            $kc = $payprice;
                            $payprice = 0;
                        }
                        //插入数据库
                        Db::table('wallet')->where('did', $did)->update(['ad_payment' => $sy]);
                        Db::table('wallet_detailed')->insert(['did' => $did, 'amount' => '-' . $kc, 'type' => 1, 'time' => date("Y-m-d H:i:s", time())]);
                    }
                }
                //确认授信额度
                if ($pay_type == 1 && $uselc < $payprice) {
                    $data = array('status' => 1, 'msg' => '授信额度不足', 'data' => '');
                } else {
                    if ($ordertype == 2 && $payprice == 0) {
                        $ordertype = 3;
                    }
                    //使用授信支付时应付金额为0
                    if ($pay_type == 1) {
                        $payprice = 0;
                    }
                    //授信支付扣除账户中的授信额度
                    if ($pay_type == 1) {
                        $newusedlc = $usedlc + $payprice;
                        //记录到授信使用表中
                        Db::table('lc_history')->insert(['did' => $did, 'amount' => $payprice, 'type' => 0, 'creattime' => date("Y-m-d H:i:s", time())]);
                        //更新账户中的授信
                        Db::table('distributor')->where('id', $did)->update(['usedlc' => $newusedlc]);
                    }
                    if ($iscard == 1) {
                        $sumnum = 0;
                    }
                    //创建订单
                    if ($invoice == 1) {
                        Db::table('order')
                            ->insert(['order_id' => $order_id, 'did' => $did, 'creat_time' => date("Y-m-d H:i:s", time()),
                                'paytype' => $pay_type, 'ordertype' => $ordertype, 'payprice' => $payprice, 'iscard' => $iscard,
                                'invoice' => $invoice, 'invoicetype' => $invoicetype, 'dremarks' => $dremarks, 'invoiceinfo' => $invoiceinfo,
                                'name' => $name, 'address' => $address, 'phone' => $phone, 'sumprice' => $sumprice, 'sumcardnum' => $sumnum]);
                    } else {
                        Db::table('order')
                            ->insert(['order_id' => $order_id, 'did' => $did, 'creat_time' => date("Y-m-d H:i:s", time()),
                                'paytype' => $pay_type, 'ordertype' => $ordertype, 'payprice' => $payprice, 'iscard' => $iscard,
                                'invoice' => $invoice, 'dremarks' => $dremarks,
                                'name' => $name, 'address' => $address, 'phone' => $phone, 'sumprice' => $sumprice, 'sumcardnum' => $sumnum]);
                    }
                    $returndata = array('order_id' => $order_id);
                    $data = array('status' => 0, 'msg' => '成功', 'data' => $returndata);
                }
            } else {
                $data = array('status' => 1, 'msg' => '系统错误，查询不到余额请联系客服', 'data' => '');
            }
        } else {
            $data = array('status' => 1, 'msg' => '代理商id错误', 'data' => '');
        }
        return json($data);
    }

    public function creatDOrder()
    {
        $did = $_REQUEST['did'];
        $iscard = $_REQUEST['iscard'];//0、卡券1、现货
        $invoice = $_REQUEST['invoice'];//发票 0不需要1需要
        if ($invoice == 1) {
            $invoicetype = $_REQUEST['invoicetype'];//发票类型 0普票 1专票
            $invoiceinfo = $_REQUEST['invoiceinfo'];//发票信息 商品名、明细
        }
        $dremarks = $_REQUEST['dremarks'];//备注
        $name = $_REQUEST['name'];//收货人名字
        $address = $_REQUEST['address'];//收货地址
        $phone = $_REQUEST['phone'];//收货人电话
        //判断订单类型 若为门店则需要区域确认，其他情况均为财务确认
        $ddata = Db::table('distributor')->where('id', $did)->find();
        if ($ddata) {
            $discount = $ddata['discount'];
            if ($ddata['type'] == 2) {
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
            $sumnum = 0;
            foreach ($shopcardata as $item) {
                $shopcarid = $item['id'];
                $goods_size_id = $item['goods_size_id'];
                $num = $item['num'];
                $sumnum += $num;
                //计算商品金额
                $goodssizedata = Db::table('goods_size')->where('id', $goods_size_id)->find();
                if ($goodssizedata) {
                    $cost = $goodssizedata['price'] * $discount;
                }
                $payprice += ($cost * $num);
                //删除当前购物车物品插入到订单详情中
                Db::table('shopcar')->where('id', $shopcarid)->delete();
                Db::table('order_details')
                    ->insert(['order_id' => $order_id, 'goods_size_id' => $goods_size_id, 'goods_num' => $num]);
            }
            if ($payprice <= 0) {
                $data = array('status' => 1, 'msg' => '订单金额为0无法下单', 'data' => '');
                return json($data);
            }
            $sumprice = $payprice;
            if ($iscard == 1) {
                $sumnum = 0;
            } else {
                //如果是卡券则每张卡券支付5元
                $payprice = 5 * $sumnum;
            }
            //创建订单
            if ($invoice == 1) {
                Db::table('order')
                    ->insert(['order_id' => $order_id, 'did' => $did, 'creat_time' => date("Y-m-d H:i:s", time()),
                        'paytype' => 0, 'ordertype' => $ordertype, 'payprice' => $payprice, 'iscard' => $iscard,
                        'invoice' => $invoice, 'invoicetype' => $invoicetype, 'dremarks' => $dremarks, 'invoiceinfo' => $invoiceinfo,
                        'name' => $name, 'address' => $address, 'phone' => $phone, 'sumprice' => $sumprice, 'sumcardnum' => $sumnum]);
            } else {
                Db::table('order')
                    ->insert(['order_id' => $order_id, 'did' => $did, 'creat_time' => date("Y-m-d H:i:s", time()),
                        'paytype' => 0, 'ordertype' => $ordertype, 'payprice' => $payprice, 'iscard' => $iscard,
                        'invoice' => $invoice, 'dremarks' => $dremarks,
                        'name' => $name, 'address' => $address, 'phone' => $phone, 'sumprice' => $sumprice, 'sumcardnum' => $sumnum]);
            }
            $returndata = array('order_id' => $order_id);
            $data = array('status' => 0, 'msg' => '成功', 'data' => $returndata);
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
        $orderdata = Db::view('order', 'order_id,did,creat_time,paytype,ordertype,payprice,iscard,name,address,phone,sumcardnum,nowcardnum')
            ->where('order.order_id', $order_id)
            ->select();
        $did = $orderdata[0]['did'];
        if ($orderdata) {
            $orderdetailsdata = Db::view('order_details', 'goods_size_id,goods_num,binding_num')
                ->view('goods_size', 'size,cost,price,card_price,size_head as headimg', 'order_details.goods_size_id=goods_size.id', 'LEFT')
                ->view('goods', 'name', 'goods_size.goods_id=goods.id', 'LEFT')
                ->where('order_details.order_id', $order_id)
                ->select();
            //查询代理商折扣
            $ddata = Db::table('distributor')->where('id', $did)->find();
            $discount = $ddata['discount'];
            $returnorderdetailsdata = array();
            foreach ($orderdetailsdata as $item) {
                $cost = $item['price'] * $discount;
                if ($item['binding_num'] >= $item['goods_num']) {
                    $needbinding = 1;
                } else {
                    $needbinding = 0;
                }
                $returnorderdetailsdata[] = array("goods_size_id" => $item['goods_size_id'],
                    "goods_num" => $item['goods_num'],
                    "size" => $item['size'],
                    "cost" => $cost,
                    "price" => $item['price'],
                    "card_price" => $item['card_price'],
                    "headimg" => $item['headimg'],
                    "name" => $item['name'],
                    "binding_num" => $item['binding_num'],
                    "needbinding" => $needbinding);
            }
            $returndata = array('orderdata' => $orderdata, 'orderdetailsdata' => $returnorderdetailsdata);
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
                $payprice = $orderdata['payprice'];
                $channerdata = Db::table('channer')->where('region_id', $did)->where('shop_id', $shop_id)->find();
                if ($channerdata) {
                    if ($payprice == 0) {
                        $ordertype = 3;
                    } else {
                        $ordertype = 2;
                    }
                    Db::table('order')->where('order_id', $order_iditem)->update(['ordertype' => $ordertype]);
                }
            }
        }
        $data = array('status' => 0, 'msg' => '成功', 'data' => '');
        return json($data);
    }

    public function myOrderList()
    {
        $did = $_REQUEST['did'];
        $orderlistdata = Db::table('order')->where('did', $did)->order('creat_time desc')->column('order_id,creat_time,paytype,ordertype,payprice,iscard');
        if ($orderlistdata) {
            $data = array('status' => 0, 'msg' => '成功', 'data' => $orderlistdata);
        } else {
            $data = array('status' => 1, 'msg' => '暂无订单', 'data' => '');
        }
        return json($data);
    }

    //待财务确认订单列表
    public function noConfirmedDOrder()
    {
        $rid = $_REQUEST['rid'];
        $dorderdata = Db::view('order', 'order_id,did,sumprice,iscard,payprice,creat_time,invoice,invoicetype,invoiceinfo')
            ->view('distributor', 'name', 'order.did=distributor.id', 'LEFT')
            ->where('ordertype', 2)
            ->order('creat_time asc')
            ->select();
        $data = array('status' => 0, 'msg' => '成功', 'data' => $dorderdata);
        return json($data);
    }

    //待出仓订单列表
    public function noDepositDOrder()
    {
        $rid = $_REQUEST['rid'];
        $dorderdata = Db::view('order', 'order_id,did,iscard,name,address,phone,sumcardnum,nowcardnum,creat_time')
            ->where('ordertype', 3)
            ->order('creat_time asc')
            ->select();
        $data = array('status' => 0, 'msg' => '成功', 'data' => $dorderdata);
        return json($data);
    }

    //已出仓订单列表
    public function depositDOrder()
    {
        $rid = $_REQUEST['rid'];
        $dorderdata = Db::view('order', 'order_id,did,iscard,name,address,phone,sumcardnum,nowcardnum,creat_time')
            ->where('ordertype', 4)
            ->order('creat_time asc')
            ->select();
        $data = array('status' => 0, 'msg' => '成功', 'data' => $dorderdata);
        return json($data);
    }

    public function dOrderComplete()
    {
        $rid = $_REQUEST['rid'];
        $order_id = $_REQUEST['orderid'];
        $express = $_REQUEST['express'];
        $express_np = $_REQUEST['express_np'];
        $orderdata = Db::table('order')->where('order_id', $order_id)->find();
        if ($orderdata) {
            if ($orderdata['ordertype'] == 3) {
                if ($orderdata['nowcardnum'] < $orderdata['sumcardnum']) {
                    $data = array('status' => 1, 'msg' => '卡券激活数量错误，无法出仓', 'data' => '');
                } else {
                    Db::table('order')->where('order_id', $order_id)->update(['ordertype' => 4, 'express' => $express, 'express_np' => $express_np]);
                    $data = array('status' => 0, 'msg' => '成功', 'data' => '');
                }
            } else {
                $data = array('status' => 1, 'msg' => '订单状态错误无法出仓', 'data' => '');
            }
        } else {
            $data = array('status' => 1, 'msg' => '订单不存在', 'data' => '');
        }
        return json($data);
    }
}