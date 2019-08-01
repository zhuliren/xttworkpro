<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/9
 * Time: 9:30
 */

namespace app\index\controller;


use app\index\model\BasicData;
use think\Db;

class User
{
    public function wxlogin()
    {
        $code = $_REQUEST['code'];
        $bdata = new BasicData();
        $appid = $bdata->getAppId();
        $secret = $bdata->getAppSecret();
        $URL = "https://api.weixin.qq.com/sns/jscode2session?appid=$appid&secret=$secret&js_code=$code&grant_type=authorization_code";
        $header[] = "Cookie: " . "appver=1.5.0.75771;";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $URL);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_REFERER, '');
        $output = curl_exec($ch);
        curl_close($ch);
        $output = json_decode($output, true);
        if (isset($output['openid']) || (isset($output['errcode']) ? $output['errcode'] : 0) == 0) {
            $openid = $output['openid'];
            //判断是否为代理商
            $distributordata = Db::table('distributor')->where('wxid', $openid)->find();
            if ($distributordata) {
                $did = $distributordata['id'];
                $name = $distributordata['name'];
                $account = $distributordata['account'];
                $type = $distributordata['type'];
                $grade = $distributordata['grade'];
                $wel = '欢迎回来 ' . $name . ' 代理商';
                $returndata = array('did' => $did, 'name' => $name, 'account' => $account, 'type' => $type, 'grade' => $grade, 'wel' =>$wel);
                $data = array('status' => 0, 'msg' => '成功', 'data' => $returndata);
            } else {
                $userdata = Db::table('user')->where('wxid', $openid)->find();
                if ($userdata) {
                    $user_id = $userdata['id'];
                } else {
                    $user_id = Db::table('user')->insertGetId(['wxid' => $openid, 'creattime' => date("Y-m-d H:i:s", time())]);
                }
                $returndata = array('openid' => $openid, 'userid' => $user_id);
                $data = array('status' => 10, 'msg' => '未绑定经销商', 'data' => $returndata);
            }
            return json($data);
        } else if ($output['errcode'] == 40029) {
            $data = array('status' => 1, 'msg' => 'code无效', 'data' => '');
            return json($data);
        } else if ($output['errcode'] == 45011) {
            $data = array('status' => 1, 'msg' => '频率限制，每个用户每分钟100次', 'data' => '');
            return json($data);
        } else if ($output['errcode'] == -1) {
            $data = array('status' => 1, 'msg' => '微信系统繁忙稍后再试', 'data' => '');
            return json($data);
        } else if ($output['errcode'] == 40163) {
            $data = array('status' => 1, 'msg' => 'code已经被使用了', 'data' => '');
            return json($data);
        }
    }

    public function login()
    {
        $openid = $_REQUEST['openid'];
        $account = $_REQUEST['account'];
        //密码双重md5加密
        $password = md5(md5($_REQUEST['password']));
        //判断密码是否正确
        $distributordata = Db::table('distributor')->where('account', $account)->where('password', $password)->find();
        if ($distributordata) {
            if ($distributordata['wxid']) {
                $data = array('status' => 1, 'msg' => '该账号已被绑定，如有问题请联系客服', 'data' => '');
            } else {
                $did = $distributordata['id'];
                $name = $distributordata['name'];
                $account = $distributordata['account'];
                $type = $distributordata['type'];
                $grade = $distributordata['grade'];
                Db::table('distributor')->where('id', $did)->update(['wxid' => $openid]);
                $returndata = array('did' => $did, 'name' => $name, 'account' => $account, 'type' => $type, 'grade' => $grade);
                $data = array('status' => 0, 'msg' => '成功', 'data' => $returndata);
            }
        } else {
            $data = array('status' => 1, 'msg' => '账号密码错误', 'data' => '');
        }
        return json($data);
    }

    public function intoUserInfo()
    {
        $user_id = $_REQUEST['userid'];
        $name = $_REQUEST['name'];
        $headimg = $_REQUEST['headimg'];
        Db::table('user')->where('id', $user_id)->update(['name' => $name, 'headimg' => $headimg]);
        $data = array('status' => 0, 'msg' => '成功', 'data' => '');
        return json($data);
    }
}