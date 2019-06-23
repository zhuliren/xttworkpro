<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/13
 * Time: 11:24
 */

namespace app\index\controller;


use think\Db;

class Distributor
{
    public function creatDistributor()
    {
        $account = $_REQUEST['account'];
        $password = md5(md5($_REQUEST['password']));
        $address = $_REQUEST['address'];
        $type = $_REQUEST['type'];
        $grade = $_REQUEST['grade'];//经销商等级 1、S+ 2、S 3、A 4、B 5、C 6、精选店 7、优选店 8、旗舰店
        $name = $_REQUEST['name'];
        $phone = $_REQUEST['phone'];
        $due = $_REQUEST['due'];
        $discount = $_REQUEST['discount'];//拿货折扣
        $remarks = $_REQUEST['remarks'];//代理商备注
        $lc = 0;
        switch ($grade) {
            case 1:
                $lc = 600000;
                break;
            case 2:
                $lc = 150000;
                break;
            case 3:
                $lc = 100000;
                break;
            case 4:
                $lc = 50000;
                break;
            case 5:
                $lc = 30000;
                break;
            case 6:
                $lc = 30000;
                break;
            case 7:
                $lc = 20000;
                break;
            case 8:
                $lc = 10000;
                break;
        }
        $did = Db::table('distributor')->insertGetId(['account' => $account,
            'password' => $password, 'address' => $address, 'type' => $type,
            'grade' => $grade, 'name' => $name, 'phone' => $phone, 'due' => $due, 'lc' => $lc, 'usedlc' => 0,
            'discount' => $discount, 'remarks' => $remarks]);
        //创建代理商钱包
        Db::table('wallet')->insert(['did' => $did]);
        $returndata = array('did' => $did);
        $data = array('status' => 0, 'msg' => '成功', 'data' => $returndata);
        return json($data);
    }

    public function updDistributorInfo()
    {
        $did = $_REQUEST['did'];
        $address = $_REQUEST['address'];
        $name = $_REQUEST['name'];
        $phone = $_REQUEST['phone'];
        $due = $_REQUEST['due'];
        $discount = $_REQUEST['discount'];//拿货折扣
        Db::table('distributor')->where('id', $did)->update(['address' => $address,
            'name' => $name, 'phone' => $phone, 'due' => $due, 'discount' => $discount]);
        $data = array('status' => 0, 'msg' => '成功', 'data' => '');
        return json($data);
    }

    public function updDistributorPwd()
    {
        $did = $_REQUEST['did'];
        $password = md5(md5($_REQUEST['password']));
        Db::table('distributor')->where('id', $did)->update(['password' => $password]);
        $data = array('status' => 0, 'msg' => '成功', 'data' => '');
        return json($data);
    }

    public function distributorInfo()
    {
        $distributor_id = $_REQUEST['did'];
        $distributordata = Db::table('distributor')->where('id', $distributor_id)->find();
        if ($distributordata) {
            $grade = $distributordata['grade'];
            //经销商等级 1、S+ 2、S 3、A 4、B 5、C 6、精选店 7、优选店 8、旗舰店
            switch ($grade) {
                case 1:
                    $message = 'S+ 级区域代理商';
                    break;
                case 2:
                    $message = 'S 级区域代理商';
                    break;
                case 3:
                    $message = 'A 级区域代理商';
                    break;
                case 4:
                    $message = 'B 级区域代理商';
                    break;
                case 5:
                    $message = 'C 级区域代理商';
                    break;
                case 6:
                    $message = '精选店 代理商';
                    break;
                case 7:
                    $message = '优选店 代理商';
                    break;
                case 8:
                    $message = '旗舰店 代理商';
                    break;
            }
            $due_time = $distributordata['due'];
            $returndata = array('message' => $message, 'duetime' => $due_time);
            $data = array('status' => 0, 'msg' => '成功', 'data' => $returndata);
        } else {
            $data = array('status' => 1, 'msg' => '代理商不存在', 'data' => '');
        }
        return json($data);
    }

    public function setMyshop()
    {
        $region_id = $_REQUEST['region_id'];
        $shop_id = $_REQUEST['shop_id'];
        //判断权限
        $regiondata = Db::table('distributor')->where('id', $region_id)->where('type', 1)->find();
        $shopdata = Db::table('distributor')->where('id', $shop_id)->where('type', 2)->select(false);
        if (!$regiondata) {
            $data = array('status' => 1, 'msg' => '区域代理id错误', 'data' => '');
        } elseif (!$shopdata) {
            $data = array('status' => 1, 'msg' => '店铺代理id错误', 'data' => '');
        } else {
            //查询shop是否绑定
            $channerdata = Db::table('channer')->where('shop_id', $shop_id)->find();
            if ($channerdata) {
                $region_id = $channerdata['region_id'];
                $data = array('status' => 10, 'msg' => '店铺已绑定区域代理商', 'data' => array('region_id' => $region_id));
            } else {
                //绑定
                Db::table('channer')->insert(['region_id' => $region_id, 'shop_id' => $shop_id]);
                $data = array('status' => 0, 'msg' => '成功', 'data' => '');
            }
        }
        return json($data);
    }

    public function showMyShop()
    {
        $region_id = $_REQUEST['did'];
        $regiondata = Db::table('distributor')->where('id', $region_id)->where('type', 1)->find();
        if ($regiondata) {
            $channerviewdata = Db::view('channer', 'region_id,shop_id')
                ->view('distributor', 'id,name,phone,grade,address,due', 'channer.shop_id=distributor.id', 'LEFT')
                ->where('channer.region_id', $region_id)
                ->select();
            $channerdata = array();
            //重组数据
            foreach ($channerviewdata as $item) {
                $itemshopid = $item['shop_id'];
                $itemordernum = Db::table('order')->where('did', $itemshopid)->count('did');
                $itemcardnum = Db::table('card')->where('did', $itemshopid)->count('did');
                $itemusedcardnum = Db::table('card')->where('did', $itemshopid)->where('type', 3)->count('did');
                $item['ordernum'] = $itemordernum;
                $item['cardnum'] = $itemcardnum;
                $item['usedcardnum'] = $itemusedcardnum;
                $channerdata[] = $item;
            }
            if ($channerviewdata) {
                $data = array('status' => 0, 'msg' => '成功', 'data' => $channerdata);
            } else {
                $data = array('status' => 1, 'msg' => '该id无店铺代理', 'data' => '');
            }
        } else {
            $data = array('status' => 1, 'msg' => '该id非区域代理id', 'data' => '');
        }
        return json($data);
    }

    public function myLC()
    {
        //我的信用额度
        $did = $_REQUEST['did'];
        $lcdata = Db::table('distributor')->where('id', $did)->find();
        if ($lcdata) {
            $lc = $lcdata['lc'];
            $usedlc = $lcdata['usedlc'];
            $lchisdata = Db::table('lc_history')->where('did', $did)->column('amount,type,creattime');
            $returndata = array('lc' => $lc, 'usedlc' => $usedlc, 'lchisdata' => $lchisdata);
            $data = array('status' => 0, 'msg' => '成功', 'data' => $returndata);
        } else {
            $data = array('status' => 1, 'msg' => '代理商id错误', 'data' => '');
        }
        return json($data);
    }

    public function getRequstOrderInfo()
    {
        $did = $_REQUEST['did'];
        $distributordata = Db::table('distributor')->where('id', $did)->find();
        if ($distributordata) {
            $name = $distributordata['name'];
            $phone = $distributordata['phone'];
            $address = $distributordata['address'];
            $returndata = array('name' => $name, 'phone' => $phone, 'address' => $address);
            $data = array('status' => 0, 'msg' => '成功', 'data' => $returndata);
        } else {
            $data = array('status' => 1, 'msg' => '代理商id错误', 'data' => '');
        }
        return json($data);
    }

    public function addBalance()
    {
        $rid = $_REQUEST['rid'];
        $did = $_REQUEST['did'];
        $amount = $_REQUEST['amount'];
        $type = $_REQUEST['type'];//1:预充值 2:装修补贴 3:赠券
        $walletdata = Db::table('wallet')->where('did', $did)->find();
        if ($walletdata) {
            $ad_payment = $walletdata['ad_payment'];
            $subsidy = $walletdata['subsidy'];
            $coupon = $walletdata['coupon'];
            switch ($type) {
                case 1:
                    $ad_payment = $ad_payment + $amount;
                    break;
                case 2:
                    $subsidy = $subsidy + $amount;
                    break;
                case 3:
                    $coupon = $coupon + $amount;
                    break;
            }
            Db::table('wallet')->where('did', $did)->update(['ad_payment' => $ad_payment, 'subsidy' => $subsidy, 'coupon' => $coupon]);
            $data = array('status' => 0, 'msg' => '成功', 'data' => '');
        } else {
            $data = array('status' => 1, 'msg' => '代理商id错误', 'data' => '');
        }
        return json($data);
    }

    public function showBalance(){
        $did = $_REQUEST['did'];
        $walletdata = Db::table('wallet')->where('did', $did)->find();
        if ($walletdata) {
            $data = array('status' => 0, 'msg' => '成功', 'data' => $walletdata);
        } else {
            $data = array('status' => 1, 'msg' => '代理商id错误', 'data' => '');
        }
        return json($data);
    }
}