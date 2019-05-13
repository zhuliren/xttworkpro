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
        $grade = $_REQUEST['grade'];
        $name = $_REQUEST['name'];
        $phone = $_REQUEST['phone'];
        $due = $_REQUEST['due'];
        $did = Db::table('distributor')->insertGetId(['account' => $account,
            'password' => $password, 'address' => $address, 'type' => $type,
            'grade' => $grade, 'name' => $name, 'phone' => $phone, 'due' => $due,]);
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
        Db::table('distributor')->where('id', $did)->update(['address' => $address,
            'name' => $name, 'phone' => $phone, 'due' => $due,]);
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
}