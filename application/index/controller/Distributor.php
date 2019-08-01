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
        $dp = $_REQUEST['dp'];//代理商税号
        $invoicename = $_REQUEST['invoicename'];//开票名称
        $bank = $_REQUEST['bank'];//开户行
        $bankacc = $_REQUEST['bankacc'];//开户行账号

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
            'discount' => $discount, 'remarks' => $remarks,
            'dp' => $dp, 'invoicename' => $invoicename, 'bank' => $bank, 'bankacc' => $bankacc]);
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
        $type = $_REQUEST['type'];
        $grade = $_REQUEST['grade'];
        $remarks = $_REQUEST['remarks'];
        $dp = $_REQUEST['dp'];
        $invoicename = $_REQUEST['invoicename'];
        $bank = $_REQUEST['bank'];
        $bankacc = $_REQUEST['bankacc'];
        Db::table('distributor')->where('id', $did)->update(['address' => $address,
            'name' => $name, 'phone' => $phone, 'due' => $due, 'discount' => $discount,
            'type' => $type, 'grade' => $grade, 'remarks' => $remarks, 'dp' => $dp, 'invoicename' => $invoicename, 'bank' => $bank, 'bankacc' => $bankacc,]);
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
            $name = $distributordata['name'];
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
            $wel = '欢迎回来 ' . $name . ' 代理商';
            $returndata = array('message' => $message, 'wel' => $wel, 'duetime' => $due_time);
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
        $type = $_REQUEST['type'];//1:预充值 3:赠券
        $remarks = $_REQUEST['remarks'];//备注
        $walletdata = Db::table('wallet')->where('did', $did)->find();
        if ($walletdata) {
            $ad_payment = $walletdata['ad_payment'];
            $coupon = $walletdata['coupon'];
            switch ($type) {
                case 1:
                    $ad_payment = $ad_payment + $amount;
                    break;
                case 2:
                    break;
                case 3:
                    $coupon = $coupon + $amount;
                    break;
            }
            Db::table('wallet')->where('did', $did)->update(['ad_payment' => $ad_payment, 'coupon' => $coupon]);
            Db::table('wallet_detailed')->insert(['did' => $did, 'amount' => $amount, 'type' => $type, 'time' => date("Y-m-d H:i:s", time()), 'remarks' => $remarks]);
            $data = array('status' => 0, 'msg' => '成功', 'data' => '');
        } else {
            $data = array('status' => 1, 'msg' => '代理商id错误', 'data' => '');
        }
        return json($data);
    }

    public function showBalance()
    {
        $did = $_REQUEST['did'];
        $walletdata = Db::table('wallet')->where('did', $did)->find();
        if ($walletdata) {
            $data = array('status' => 0, 'msg' => '成功', 'data' => $walletdata);
        } else {
            $data = array('status' => 1, 'msg' => '代理商id错误', 'data' => '');
        }
        return json($data);
    }

    public function changePwd()
    {
        $did = $_REQUEST['did'];
        $oldpwd = md5(md5($_REQUEST['oldpwd']));
        $newpwd = md5(md5($_REQUEST['newpwd']));
        $ddata = Db::table('distributor')->where('id', $did)->find();
        if ($ddata) {
            if ($oldpwd == $ddata['password']) {
                Db::table('distributor')->where('id', $did)->update(['password' => $newpwd]);
                $data = array('status' => 0, 'msg' => '修改成功', 'data' => '');
            } else {
                $data = array('status' => 1, 'msg' => '原始密码错误', 'data' => '');
            }
        } else {
            $data = array('status' => 1, 'msg' => '代理商id错误', 'data' => '');
        }
        return json($data);
    }

    public function useCard()
    {
        $did = $_REQUEST['did'];
        $acc = $_REQUEST['acc'];
        $pwd = md5(md5($_REQUEST['pwd']));
        //查询卡券
        $carddata = Db::table('card')->where('acc', $acc)->find();
        if ($carddata) {
            if ($pwd == $carddata['pwd']) {
                if ($carddata['type'] == 2) {
                    //修改卡券状态
                    Db::table('card')->where('acc', $acc)->update(['type' => 3, 'isdused' => 1, 'useddid' => $did, 'used_time' => date("Y-m-d H:i:s", time())]);
                    $data = array('status' => 0, 'msg' => '成功', 'data' => '');
                } else {
                    if ($carddata['type'] == 3) {
                        $data = array('status' => 1, 'msg' => '卡券已使用', 'data' => '');
                    } else if ($carddata['type'] == 0) {
                        $data = array('status' => 1, 'msg' => '卡券无法使用', 'data' => '');
                    } else if ($carddata['type'] == 1) {
                        $data = array('status' => 1, 'msg' => '卡券未激活', 'data' => '');
                    }
                }
            } else {
                $data = array('status' => 1, 'msg' => '卡券密码错误', 'data' => '');
            }
        } else {
            $data = array('status' => 1, 'msg' => '卡券账号不存在', 'data' => '');
        }
        return json($data);
    }

    public function myWallet()
    {
        $did = $_REQUEST['did'];
        $walletdata = Db::table('wallet')->where('did', $did)->find();
        if ($walletdata) {
            $detaileddata = Db::table('wallet_detailed')->where('did', $did)->order('time desc')->column('id,amount,type,time');
            $returndata = array('info' => $walletdata, 'detailed' => $detaileddata);
            $data = array('status' => 0, 'msg' => '成功', 'data' => $returndata);
        } else {
            $data = array('status' => 1, 'msg' => '代理商id错误', 'data' => '');
        }
        return json($data);
    }

    public function showInvoiceInfo()
    {
        $did = $_REQUEST['did'];
        $ddata = Db::table('distributor')->where('id', $did)->find();
        if ($ddata) {
            $returndata = array('dp' => $ddata['dp'], 'invoicename' => $ddata['invoicename'], 'bank' => $ddata['bank'], 'bankacc' => $ddata['bankacc']);
            $data = array('status' => 0, 'msg' => '成功', 'data' => $returndata);
        } else {
            $data = array('status' => 1, 'msg' => '代理商id错误', 'data' => '');
        }
        return json($data);
    }

    public function logOff()
    {
        $did = $_REQUEST['did'];
        Db::table('distributor')->where('id', $did)->update(['wxid' => '']);
        $data = array('status' => 0, 'msg' => '成功', 'data' => '');
        return json($data);
    }

    public function dInfo()
    {
        $did = $_REQUEST['did'];
        $ddata = Db::table('distributor')->where('id', $did)->find();
        if ($ddata) {
            $data = array('status' => 0, 'msg' => '成功', 'data' => $ddata);
        } else {
            $data = array('status' => 0, 'msg' => '代理商id错误', 'data' => '');
        }
        return json($data);
    }

    public function resetDPwd()
    {
        $did = $_REQUEST['did'];
        $password = md5(md5($_REQUEST['newpwd']));
        Db::table('distributor')->where('id', $did)->update(['password' => $password]);
        $data = array('status' => 0, 'msg' => '成功', 'data' => '');
        return json($data);
    }

    //区域代理商列表
    public function DForRegion()
    {
        $rid = $_REQUEST['rid'];
        $ddata = Db::table('distributor')->where('type', 1)->select();
        $returndata = array();
        foreach ($ddata as $item) {
            $returndata[] = array('id' => $item['id'], 'name' => $item['name']);
        }
        $data = array('status' => 0, 'msg' => '成功', 'data' => $returndata);
        return json($data);
    }

    //当前区域代理商
    public function nowRegion()
    {
        $rid = $_REQUEST['rid'];
        $did = $_REQUEST['did'];
        $ddata = Db::table('distributor')->where('id', $did)->where('type', 2)->find();
        if ($ddata) {
            $cdata = Db::table('channer')->where('shop_id', $did)->find();
            if ($cdata) {
                $rddata = Db:: table('distributor')->where('id', $cdata['region_id'])->where('type', 1)->find();
                $returndata = array('did' => $rddata['id'], 'name' => $rddata['name']);
                $data = array('status' => 0, 'msg' => '成功', 'data' => $returndata);
            } else {
                $data = array('status' => 10, 'msg' => '无上级区域代理', 'data' => '');
            }
        } else {
            $data = array('status' => 1, 'msg' => '非门店代理无法进行此操作', 'data' => '');
        }
        return json($data);
    }

    public function bindingRegion()
    {
        $rid = $_REQUEST['rid'];
        $did = $_REQUEST['did'];
        $rdid = $_REQUEST['rdid'];
        $ddata = Db::table('distributor')->where('id', $did)->where('type', 2)->find();
        if ($ddata) {
            $rddata = Db::table('distributor')->where('id', $rdid)->where('type', 1)->find();
            if ($rddata) {
                $cdata = Db::table('channer')->where('shop_id', $did)->find();
                if ($cdata) {
                    Db::table('channer')->where('shop_id', $did)->update(['region_id' => $rdid]);
                } else {
                    Db::table('channer')->insert(['region_id' => $rdid, 'shop_id' => $did]);
                }
                $data = array('status' => 0, 'msg' => '成功', 'data' => '');
            } else {
                $data = array('status' => 1, 'msg' => '区域代理商id错误', 'data' => '');
            }
        } else {
            $data = array('status' => 1, 'msg' => '非门店代理无法进行此操作', 'data' => '');
        }
        return json($data);
    }

    public function dList()
    {
        $ddata = Db::table('distributor')->where('logout', 0)->order('id asc')->select();
        $returndata = array();
        foreach ($ddata as $item) {
            $returndata[] = array('did' => $item['id'], 'dname' => $item['name']);
        }
        $data = array('status' => 0, 'msg' => '成功', 'data' => $returndata);
        return json($data);
    }
}