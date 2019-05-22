<?php


namespace app\index\controller;


use think\Db;

class Card
{
    public function creatCard()
    {
        $num = $_REQUEST['num'];
        if ($num > 300) {
            $data = array('status' => 1, 'msg' => '单词创建请勿超过300张，防止系统卡顿', 'data' => '');
        } else {
            //密码包含规则
            $rule = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
            //密码位数
            $limit = 8;
            //先创建账号
            for ($i = 0; $i < $num; $i++) {
                //创建6位随机数密码 并加密
                $rand = implode("", array_rand($rule, $limit));
                $pwd = md5(md5($rand));
                $cardid = Db::table('card')->insertGetId(['pwd' => $pwd, 'creat_time' => date("Y-m-d H:i:s", time())]);
                //生成卡编号 以id为主 11位
                $acc = sprintf("%011d", $cardid);
                Db::table('card')->where('id', $cardid)->update(['acc' => $acc]);
                Db::table('card_e_info')->insert(['pwd' => $rand, 'creat_time' => date("Y-m-d H:i:s", time()), 'acc' => $acc]);
                if ($i == 0) {
                    $firstacc = $acc;
                }
                if ($i <= $num) {
                    $lastacc = $acc;
                }
            }
            $returndata = array('firstacc' => $firstacc, 'lastacc' => $lastacc, 'num' => $num);
            $data = array('status' => 0, 'msg' => '成功', 'data' => $returndata);
        }
        return json($data);
    }

    public function getCardAccLikeLast()
    {
        $did = $_REQUEST['did'];
        $like_string = $_REQUEST['likestring'];
        $carddata = Db::view('card', 'acc,type,binding_time,gsid')
            ->view('goods_size', 'size', 'card.gsid=goods_size.id', 'LEFT')
            ->view('goods', 'name,headimg', 'goods_size.goods_id=goods.id')
            ->where('card.did', $did)
            ->where('card.acc', 'like', '%' . $like_string)
            ->order('card.acc asc')
            ->select();
        if ($carddata) {
            $data = array('status' => 0, 'msg' => '成功', 'data' => $carddata);
        } else {
            $data = array('status' => 1, 'msg' => '无相似卡号', 'data' => '');
        }
        return json($data);
    }

    public function getCardInfo()
    {
        $acc = $_REQUEST['acc'];
        //判断用户身份
        $carddata = Db::view('card', 'acc,type,binding_time,gsid')
            ->view('goods_size', 'size', 'card.gsid=goods_size.id', 'LEFT')
            ->view('goods', 'name,headimg', 'goods_size.goods_id=goods.id')
            ->where('card.acc', $acc)
            ->order('card.acc asc')
            ->select(false);
        if ($carddata) {
            $data = array('status' => 0, 'msg' => '成功', 'data' => $carddata);
        } else {
            $data = array('status' => 1, 'msg' => '无相似卡号', 'data' => '');
        }
        return json($data);
    }

    public function bindingCard()
    {
        $rid = $_REQUEST['rid'];
        $did = $_REQUEST['did'];
        $gsid = $_REQUEST['gsid'];
        $acc = $_REQUEST['acc'];
        //查询卡券是否存在
        $carddata = Db::table('card')->where('acc', $acc)->find();
        if ($carddata) {
            $type = $carddata['type'];
            if ($type == 0) {
                Db::table('card')->where('acc', $acc)->update(['rid' => $rid, 'did' => $did, 'binding_time' => date("Y-m-d H:i:s", time()), 'gsid' => $gsid, 'type' => 1]);
                $data = array('status' => 0, 'msg' => '成功', 'data' => '');
            } else {
                $data = array('status' => 1, 'msg' => '卡券已出库', 'data' => '');
            }
        } else {
            $data = array('status' => 1, 'msg' => '卡券账号不存在', 'data' => '');
        }
        return json($data);
    }

    public function actCard()
    {
        $did = $_REQUEST['did'];
        $acc = $_REQUEST['acc'];
        $carddata = Db::table('card')->where('did', $did)->where('acc', $acc)->find();
        if ($carddata) {
            $type = $carddata['type'];
            if ($type == 1) {
                Db::table('card')->where('acc', $acc)->update(['type' => 2, 'act_time' => date("Y-m-d H:i:s", time())]);
                $data = array('status' => 0, 'msg' => '成功', 'data' => '');
            } else {
                $data = array('status' => 1, 'msg' => '卡券状态无法激活', 'data' => '');
            }
        } else {
            $data = array('status' => 1, 'msg' => '卡券账号不存在', 'data' => '');
        }
        return json($data);
    }

    public function userGetCardInfo()
    {
        $acc = $_REQUEST['acc'];
        $pwd = md5(md5($_REQUEST['pwd']));
        //查询卡券
        $carddata = Db::table('card')->where('acc', $acc)->find();
        if ($carddata) {
            if ($pwd == $carddata['pwd']) {
                $gsid = $carddata['gsid'];
                $goodsdata = Db::view('goods_size', 'size,card_price')
                    ->view('goods', 'name,headimg', 'goods_size.goods_id=goods.id', 'LEFT')
                    ->where('goods_size.id', $gsid)
                    ->select();
                $data = array('status' => 0, 'msg' => '成功', 'data' => $goodsdata);
            } else {
                $data = array('status' => 1, 'msg' => '卡券密码错误', 'data' => '');
            }
        } else {
            $data = array('status' => 1, 'msg' => '卡券账号不存在', 'data' => '');
        }
        return json($data);
    }

    public function cardList()
    {
        $limit = $_REQUEST['limit'];
        $page = $_REQUEST['page'];
        $start = $page * $limit;
        $selectgoods = Db::table('card_e_info')->limit($start, $limit)->column('acc,pwd,creat_time');
        if ($selectgoods) {
            $data = array('status' => 0, 'msg' => '成功', 'data' => $selectgoods);
        } else {
            $data = array('status' => 1, 'msg' => '无卡券', 'data' => '');
        }
        return json($data);
    }

    public function cardListAll()
    {
        $limit = $_REQUEST['limit'];
        $page = $_REQUEST['page'];
        $start = $page * $limit;
        $selectgoods = Db::table('card_')->limit($start, $limit)->column('acc,type,creat_time');
        if ($selectgoods) {
            $data = array('status' => 0, 'msg' => '成功', 'data' => $selectgoods);
        } else {
            $data = array('status' => 1, 'msg' => '无卡券', 'data' => '');
        }
        return json($data);
    }

    public function actCardLot()
    {
        $did = $_REQUEST['did'];
        $fcardacc = $_REQUEST['fcardacc'];
        $lcardacc = $_REQUEST['lcardacc'];
        //查询首卡id
        $fcarddata = Db::table('card')->where('acc', $fcardacc)->find();
        if ($fcarddata) {
            $lcarddata = Db::table('card')->where('acc', $lcardacc)->find();
            if ($lcarddata) {
                $fid = $fcarddata['id'];
                $lid = $lcarddata['id'];
                if ($fid > $lid) {
                    $data = array('status' => 1, 'msg' => '超出范围', 'data' => '');
                } else {
                    for ($i = $fid; $i < $lid; $i++) {

                    }
                }
            } else {
                $data = array('status' => 1, 'msg' => '末张卡券账号不存在', 'data' => '');
            }
        } else {
            $data = array('status' => 1, 'msg' => '首张卡券账号不存在', 'data' => '');
        }
        return json($data);
    }
}