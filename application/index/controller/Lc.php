<?php


namespace app\index\controller;


use think\Db;

class Lc
{
    public function myLc()
    {
        $did = $_REQUEST['did'];
        $ddata = Db::table('distributor')->where('id', $did)->find();
        if ($ddata) {
            $lc = $ddata['lc'];
            $usedlc = $ddata['usedlc'];
            $canlc = $lc - $usedlc;
            $lchdata = Db::table('lc_history')->where('did', $did)->column('amount,type,creattime');
            $lcinfo = array('lc' => $lc, 'usedlc' => $usedlc, 'canlc' => $canlc);
            $lchistory = $lchdata;
            $returndata = array('lcinfo' => $lcinfo, 'lchistory' => $lchistory);
            $data = array('status' => 0, 'msg' => '成功', 'data' => $returndata);
        } else {
            $data = array('status' => 1, 'msg' => '代理商id错误', 'data' => '');
        }
        return json($data);
    }

    public function lcHis()
    {
        $did = $_REQUEST['did'];
        $ddata = Db::table('distributor')->where('id', $did)->find();
        if ($ddata) {
            $lchdata = Db::table('lc_history')->where('did', $did)->column('amount,type,creattime');
            if ($lchdata) {
                $data = array('status' => 0, 'msg' => '成功', 'data' => $lchdata);
            } else {
                $data = array('status' => 1, 'msg' => '无使用授信记录', 'data' => '');
            }
        } else {
            $data = array('status' => 1, 'msg' => '代理商id错误', 'data' => '');
        }
        return json($data);
    }

    public function reLc()
    {
        $did = $_REQUEST['did'];
        $amount = $_REQUEST['amount'];
        $ddata = Db::table('distributor')->where('id', $did)->find();
        if ($ddata) {
            $usedlc = $ddata['usedlc'];
            if ($amount > $usedlc) {
                $data = array('status' => 1, 'msg' => '恢复授信金额超出限制', 'data' => '');
            } else {
                $newusedlc = $usedlc - $amount;
                Db::table('distributor')->where('id', $did)->update(['usedlc' => $newusedlc]);
                Db::table('lc_history')->insert(['did' => $did, 'amount' => $amount, 'type' => 0, 'creattime' => date("Y-m-d H:i:s", time())]);
                $data = array('status' => 0, 'msg' => '成功', 'data' => '');
            }
        } else {
            $data = array('status' => 1, 'msg' => '代理商id错误', 'data' => '');
        }
        return json($data);
    }

    public function changeLc()
    {
        $did = $_REQUEST['did'];
        $newlc = $_REQUEST['newlc'];
        $ddata = Db::table('distributor')->where('id', $did)->find();
        if ($ddata) {
            Db::table('distributor')->where('id', $did)->update(['lc' => $newlc]);
            $data = array('status' => 0, 'msg' => '成功', 'data' => '');
        } else {
            $data = array('status' => 1, 'msg' => '代理商id错误', 'data' => '');
        }
        return json($data);
    }
}