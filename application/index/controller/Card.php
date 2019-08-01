<?php


namespace app\index\controller;


use think\Db;

class Card
{
    public function getCardType()
    {
        $cardtypedata = Db::table('cardtype')->column(['id,name,no']);
        if ($cardtypedata) {
            $data = array('status' => 0, 'msg' => '成功', 'data' => $cardtypedata);
        } else {
            $data = array('status' => 1, 'msg' => '无信息', 'data' => '');
        }
        return json($data);
    }

    public function setCardType()
    {
        $name = $_REQUEST['name'];
        $no = $_REQUEST['no'];
        $cardtypeid = Db::table('cardtype')->insertGetId(['name' => $name, 'no' => $no]);
        if ($cardtypeid) {
            $data = array('status' => 0, 'msg' => '成功', 'data' => array('cardtypeid' => $cardtypeid));
        } else {
            $data = array('status' => 1, 'msg' => '失败', 'data' => '');
        }
        return json($data);
    }

    public function delCardType()
    {
        $id = $_REQUEST['id'];
        Db::table('cardtype')->where('id', $id)->delete();
        $data = array('status' => 0, 'msg' => '成功', 'data' => '');
        return json($data);
    }

    public function newCreatCard()
    {
        $rid = $_REQUEST['rid'];
        $num = $_REQUEST['num'];
        $cardtype = $_REQUEST['typeid'];
        $goodsid = $_REQUEST['goodsid'];
        $gsid = $_REQUEST['gsid'];
        if ($num > 10000) {
            $data = array('status' => 1, 'msg' => '单词创建请勿超过10000张，防止系统卡顿', 'data' => '');
        } else {
            //密码位数
            $limit = 8;
            //卡号第一位是品牌，第二第三位是产品编号，第四到第七位是型号，第八到第十二位是券号连号数列
            $cardtypedata = Db::table('cardtype')->where('id', $cardtype)->find();
            $brandid = $cardtypedata['no'];//品牌编号
            $goodsdata = Db::table('goods')->where('id', $goodsid)->find();
            $goodsno = sprintf("%02d", $goodsdata['goodsno']);//产品编号
            $goodssizedata = Db::table('goods_size')->where('id', $gsid)->find();
            $modelid = sprintf("%04d", $goodssizedata['modelid']);//券号
            $acchead = $brandid . $goodsno . $modelid;
            //初始卡券连号数列
            $cardfnumdata = Db::table('cardnum')->where('gsid', $gsid)->find();
            $cardfnum = $cardfnumdata['nownum'];
            //先创建账号
            for ($i = 0; $i < $num; $i++) {
                //创建6位随机数密码 并加密
                $rand = substr(rand(100000000, 9999999999), 1, $limit);
                $pwd = md5(md5($rand));
                //生成卡编号 以id为主 11位
                $accend = sprintf("%06d", $cardfnum);
                $cardfnum++;
                $acc = $acchead . $accend;
                Db::table('card')->insert(['pwd' => $pwd, 'creat_time' => date("Y-m-d H:i:s", time()), 'acc' => $acc, 'gsid' => $gsid, 'no' => $brandid, 'goodsno' => $goodsno, 'modelid' => $modelid, 'pwdinfo' => $rand]);
                if ($i == 0) {
                    $firstacc = $acc;
                }
                if ($i <= $num) {
                    $lastacc = $acc;
                }
            }
            //更新数量
            Db::table('cardnum')->where('gsid', $gsid)->update(['nownum' => $cardfnum]);
            $returndata = array('firstacc' => $firstacc, 'lastacc' => $lastacc, 'num' => $num);
            $data = array('status' => 0, 'msg' => '成功', 'data' => $returndata);
        }
        return json($data);
    }

    public function creatCardByNum()
    {
        $rid = $_REQUEST['rid'];
        $startnum = $_REQUEST['startnum'];
        $endum = $_REQUEST['endnum'];
        $cardtype = $_REQUEST['typeid'];
        $goodsid = $_REQUEST['goodsid'];
        $gsid = $_REQUEST['gsid'];
        $num = $endum - $startnum + 1;
        if ($num > 10000) {
            $data = array('status' => 1, 'msg' => '单词创建请勿超过10000张，防止系统卡顿', 'data' => '');
        } else {
            //密码位数
            $limit = 8;
            //卡号第一位是品牌，第二第三位是产品编号，第四到第七位是型号，第八到第十二位是券号连号数列
            $cardtypedata = Db::table('cardtype')->where('id', $cardtype)->find();
            $brandid = $cardtypedata['no'];//品牌编号
            $goodsdata = Db::table('goods')->where('id', $goodsid)->find();
            $goodsno = sprintf("%02d", $goodsdata['goodsno']);//产品编号
            $goodssizedata = Db::table('goods_size')->where('id', $gsid)->find();
            $modelid = sprintf("%04d", $goodssizedata['modelid']);//券号
            $acchead = $brandid . $goodsno . $modelid;
            //初始卡券连号数列
            $cardfnumdata = Db::table('cardnum')->where('gsid', $gsid)->find();
            $cardfnum = $cardfnumdata['nownum'];
            //先创建账号
            for ($i = $startnum; $i <= $endum; $i++) {
                //创建6位随机数密码 并加密
                $rand = substr(rand(100000000, 9999999999), 1, $limit);
                $pwd = md5(md5($rand));
                //生成卡编号 以id为主 11位
                $accend = sprintf("%06d", $i);
                $cardfnum++;
                $acc = $acchead . $accend;
                Db::table('card')->insert(['pwd' => $pwd, 'creat_time' => date("Y-m-d H:i:s", time()), 'acc' => $acc, 'gsid' => $gsid, 'no' => $brandid, 'goodsno' => $goodsno, 'modelid' => $modelid, 'pwdinfo' => $rand]);
                if ($i == $startnum) {
                    $firstacc = $acc;
                }
                if ($i <= $endum) {
                    $lastacc = $acc;
                }
            }
            //更新数量
            Db::table('cardnum')->where('gsid', $gsid)->update(['nownum' => $cardfnum]);
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
            ->view('goods_size', 'size,size_head as headimg', 'card.gsid = goods_size.id', 'LEFT')
            ->view('goods', 'name', 'goods_size.goods_id = goods.id')
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
            ->view('goods_size', 'size', 'card.gsid = goods_size.id', 'LEFT')
            ->view('goods', 'name,headimg', 'goods_size.goods_id = goods.id')
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
        $orderid = $_REQUEST['orderid'];
        $orderdata = Db::table('order')->where('order_id', $orderid)->find();
        $nowcardnum = $orderdata['nowcardnum'];
        //检查卡券是否与需要激活的商品规格相同
        $fcarddata = Db::table('card')->where('acc', $acc)->find();
        if ($fcarddata['gsid'] != $gsid) {
            $data = array('status' => 1, 'msg' => '卡券与商品规格不匹配', 'data' => '');
            return json($data);
        }
        //查询订单该规格是否已出仓全部卡券
        $oddata = Db::table('order_details')->where('goods_size_id', $gsid)->where('order_id', $orderid)->find();
        if ($oddata) {
            $goodsnum = $oddata['goods_num'];
            $bindingnum = $oddata['binding_num'];
            $ddid = $oddata['id'];
            if ($bindingnum < $goodsnum) {
                //查询卡券是否存在
                $carddata = Db::table('card')->where('acc', $acc)->find();
                if ($carddata) {
                    $type = $carddata['type'];
                    if ($type == 0) {
                        Db::table('card')->where('acc', $acc)->update(['rid' => $rid, 'did' => $did, 'binding_time' => date("Y-m-d H:i:s", time()), 'gsid' => $gsid, 'type' => 1]);
                        $newbindingnum = $bindingnum + 1;
                        Db::table('order_details')->where('id', $ddid)->update(['binding_num' => $newbindingnum]);
                        $nowcardnum += 1;
                        Db::table('order')->where('order_id', $orderid)->update(['nowcardnum' => $nowcardnum]);
                        $orddata = Db::table('order_details')->where('id', $ddid)->find();
                        if ($orddata['binding_num'] >= $orddata['goods_num']) {
                            $needbinding = 1;
                        } else {
                            $needbinding = 0;
                        }
                        $data = array('status' => 0, 'msg' => '成功', 'data' => array('needbinding' => $needbinding));
                    } else {
                        $data = array('status' => 1, 'msg' => '卡券已出库', 'data' => '');
                    }
                } else {
                    $data = array('status' => 1, 'msg' => '卡券账号不存在', 'data' => '');
                }
            } else {
                $data = array('status' => 1, 'msg' => '激活失败，卡券已出仓完毕', 'data' => '');
            }
        } else {
            $data = array('status' => 1, 'msg' => '订单信息错误', 'data' => '');
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
                //判断是否存在财务确认卡券列表
                $adata = Db::table('awaitactcard')->where('did', $did)->where('acc', $acc)->find();
                if ($adata) {
                    if ($adata['type'] == 0) {
                        $data = array('status' => 1, 'msg' => '已提交激活申请，等待确认激活', 'data' => '');
                    } else {
                        $data = array('status' => 1, 'msg' => '卡券已激活', 'data' => '');
                    }
                } else {
                    //申城卡券激活确认订单号
                    $oid = $did . date('Ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
                    //插入待财务确认卡券列表
                    Db::table('awaitactcard')->insert(['did' => $did, 'acc' => $acc, 'creat_time' => date("Y-m-d H:i:s", time()), 'type' => 0, 'oid' => $oid]);
                    //查询应付金额
                    $gsid = $carddata['gsid'];
                    $gsdata = Db::table('goods_size')->where('id', $gsid)->find();
                    $price = $gsdata['price'];
                    //查询折扣
                    $ddata = Db::table('distributor')->where('id', $did)->find();
                    $discount = $ddata['discount'];
                    $payprice = $price * $discount;
                    $data = array('status' => 0, 'msg' => '激活申请提交成功', 'data' => array('payprice' => $payprice));
                }
            } else {
                $data = array('status' => 1, 'msg' => '卡券状态无法激活', 'data' => '');
            }
        } else {
            $data = array('status' => 1, 'msg' => '卡券账号不存在', 'data' => '');
        }
        return json($data);
    }

    public function actCardByRid()
    {
        $rid = $_REQUEST['rid'];
        $did = $_REQUEST['did'];
        $acc = $_REQUEST['acc'];
        $carddata = Db::table('card')->where('did', $did)->where('acc', $acc)->find();
        if ($carddata) {
            $type = $carddata['type'];
            if ($type == 1) {
                Db::table('awaitactcard')->where('acc', $acc)->update(['type' => 1, 'confirm_time' => date("Y-m-d H:i:s", time())]);
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

    public function actCardByRidFromOid()
    {
        $rid = $_REQUEST['rid'];
        $did = $_REQUEST['did'];
        $oid = $_REQUEST['oid'];
        //查询该代理商名下需要激活的卡券 group by oid
        $adata = Db::table('awaitactcard')->where('did', $did)->where('oid', $oid)->select();
        foreach ($adata as $item) {
            $acc = $item['acc'];
            $carddata = Db::table('card')->where('did', $did)->where('acc', $acc)->find();
            if ($carddata) {
                $type = $carddata['type'];
                if ($type == 1) {
                    Db::table('awaitactcard')->where('acc', $acc)->update(['type' => 1, 'confirm_time' => date("Y-m-d H:i:s", time())]);
                    Db::table('card')->where('acc', $acc)->update(['type' => 2, 'act_time' => date("Y-m-d H:i:s", time())]);
                }
            }
            $data = array('status' => 0, 'msg' => '成功', 'data' => '');
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
                    ->view('goods', 'name,headimg', 'goods_size.goods_id = goods.id', 'LEFT')
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

    public function cardListAll()
    {
        $limit = $_REQUEST['limit'];
        $page = $_REQUEST['page'];
        $start = $page * $limit;
        $cardnum = Db::table('card')->count('id');
        $selectgoods = Db::table('card')->limit($start, $limit)->column('id,acc,type,creat_time,no,goodsno,modelid');
        if ($selectgoods) {
            $returndata = array('card_info' => $selectgoods, 'card_num' => $cardnum);
            $data = array('status' => 0, 'msg' => '成功', 'data' => $returndata);
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
        //查询折扣
        $ddata = Db::table('distributor')->where('id', $did)->find();
        $discount = $ddata['discount'];
        $payprice = 0;
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
                    //申城卡券激活确认订单号
                    $oid = $did . date('Ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
                    for ($i = $fid; $i <= $lid; $i++) {
                        $carddata = Db::table('card')->where('did', $did)->where('id', $i)->find();
                        if ($carddata) {
                            $type = $carddata['type'];
                            if ($type == 1) {
                                $adata = Db::table('awaitactcard')->where('acc', $carddata['acc'])->find();
                                if ($adata) {
                                } else {
                                    //查询应付金额
                                    $gsid = $carddata['gsid'];
                                    $gsdata = Db::table('goods_size')->where('id', $gsid)->find();
                                    $price = $gsdata['price'];
                                    //计算当前价格
                                    $nowprice = $price * $discount;
                                    $payprice += $nowprice;
                                    //插入待财务确认卡券列表
                                    Db::table('awaitactcard')->insert(['did' => $did, 'acc' => $carddata['acc'], 'creat_time' => date("Y-m-d H:i:s", time()), 'type' => 0, 'oid' => $oid]);
                                }
                            }
                        }
                    }
                    $data = array('status' => 0, 'msg' => '成功', 'data' => array('payprice' => $payprice));
                }
            } else {
                $data = array('status' => 1, 'msg' => '末张卡券账号不存在', 'data' => '');
            }
        } else {
            $data = array('status' => 1, 'msg' => '首张卡券账号不存在', 'data' => '');
        }
        return json($data);
    }

    public function bindingCardLot()
    {
        $rid = $_REQUEST['rid'];
        $did = $_REQUEST['did'];
        $gsid = $_REQUEST['gsid'];
        $fcardacc = $_REQUEST['fcardacc'];
        $lcardacc = $_REQUEST['lcardacc'];
        $orderid = $_REQUEST['orderid'];
        $orderdata = Db::table('order')->where('order_id', $orderid)->find();
        $nowcardnum = $orderdata['nowcardnum'];
        //查询首卡id
        $fcarddata = Db::table('card')->where('acc', $fcardacc)->find();
        $lcarddata = Db::table('card')->where('acc', $lcardacc)->find();
        //判断卡券是否匹配
        if ($fcarddata) {
            if ($lcarddata) {
                if ($fcarddata['gsid'] != $lcarddata['gsid']) {
                    $data = array('status' => 1, 'msg' => '首末卡券非同一商品规格', 'data' => '');
                    return json($data);
                }
            } else {
                $data = array('status' => 1, 'msg' => '末张卡券账号不存在', 'data' => '');
                return json($data);
            }
        } else {
            $data = array('status' => 1, 'msg' => '首张卡券账号不存在', 'data' => '');
            return json($data);
        }
        //检查卡券是否与需要激活的商品规格相同
        if ($fcarddata['gsid'] != $gsid) {
            $data = array('status' => 1, 'msg' => '卡券与商品规格不匹配', 'data' => '');
            return json($data);
        }
        //查询订单该规格是否已出仓全部卡券
        $oddata = Db::table('order_details')->where('goods_size_id', $gsid)->where('order_id', $orderid)->find();
        if ($oddata) {
            $goodsnum = $oddata['goods_num'];
            $bindingnum = $oddata['binding_num'];
            $ddid = $oddata['id'];
            if ($bindingnum >= $goodsnum) {
                $data = array('status' => 1, 'msg' => '激活失败，卡券已全部出仓', 'data' => '');
                return json($data);
            }
        } else {
            $data = array('status' => 1, 'msg' => '订单信息错误', 'data' => '');
            return json($data);
        }
        if ($fcarddata) {
            if ($lcarddata) {
                $fid = $fcarddata['id'];
                $lid = $lcarddata['id'];
                if ($fid > $lid) {
                    $data = array('status' => 1, 'msg' => '超出范围', 'data' => '');
                } else {
                    $nownum = 0;
                    for ($i = $fid; $i <= $lid; $i++) {
                        $carddata = Db::table('card')->where('id', $i)->find();
                        if ($carddata) {
                            $type = $carddata['type'];
                            if ($type == 0) {
                                Db::table('card')->where('id', $i)->update(['rid' => $rid, 'did' => $did, 'binding_time' => date("Y-m-d H:i:s", time()), 'gsid' => $gsid, 'type' => 1]);
                                $nownum++;
                                $newbindingnum = $bindingnum + $nownum;
                                if ($newbindingnum <= $goodsnum) {
                                    Db::table('order_details')->where('id', $ddid)->update(['binding_num' => $newbindingnum]);
                                } else {
                                    $data = array('status' => 1, 'msg' => '已出仓' . $nownum . '张卡券，全部出仓完毕', 'data' => array('needbinding' => 1));
                                    $nowcardnum += $nownum;
                                    Db::table('order')->where('order_id', $orderid)->update(['nowcardnum' => $nowcardnum]);
                                    return json($data);
                                }
                            } else {
                                $data = array('status' => 1, 'msg' => '卡券号' . $carddata['acc'] . '已出仓,后续卡券暂停激活', 'data' => '');
                                return json($data);
                            }
                        }
                    }
                    $nowcardnum += $nownum;
                    Db::table('order')->where('order_id', $orderid)->update(['nowcardnum' => $nowcardnum]);
                    $orddata = Db::table('order_details')->where('id', $ddid)->find();
                    if ($orddata['binding_num'] >= $orddata['goods_num']) {
                        $needbinding = 1;
                    } else {
                        $needbinding = 0;
                    }
                    $data = array('status' => 0, 'msg' => '成功', 'data' => array('needbinding' => $needbinding));
                }
            } else {
                $data = array('status' => 1, 'msg' => '末张卡券账号不存在', 'data' => '');
            }
        } else {
            $data = array('status' => 1, 'msg' => '首张卡券账号不存在', 'data' => '');
        }
        return json($data);
    }

    public function getCardAllInfo()
    {
        $did = $_REQUEST['did'];
        $acc = $_REQUEST['acc'];
        $ddata = Db::table('distributor')->where('id', $did)->find();
        if ($ddata) {
            $carddata = Db::view('card', 'acc,type,creat_time,binding_time,act_time,used_time,no,goodsno,modelid')
                ->view('root', 'acc as racc', 'card.rid = root.id', 'LEFT')
                ->view('distributor', 'name as dname,type as dtype,grade as dgrade', 'card.rid = distributor.id', 'LEFT')
                ->view('user', 'name as uname', 'card.user_id = user.id', 'LEFT')
                ->view('goods_size', 'size as gsize,modelid', 'card.gsid = goods_size.id', 'LEFT')
                ->view('goods', 'name as gname,goodsno', 'goods_size.goods_id = goods.id', 'RIGHT')
                ->where('card.acc', $acc)
                ->select();
            if ($carddata) {
                $data = array('status' => 0, 'msg' => '成功', 'data' => $carddata);
            } else {
                $data = array('status' => 1, 'msg' => '账号错误', 'data' => '');
            }
        } else {
            $data = array('status' => 1, 'msg' => '无权限查看', 'data' => '');
        }
        return json($data);
    }

    public function selCardPwdInfo()
    {
        $rid = $_REQUEST['rid'];
        $gsid = $_REQUEST['gsid'];//规格id 0全部
        $isexport = $_REQUEST['isexport'];//导出状态 0未导出 1已导出 2全部
        $type = $_REQUEST['type'];//操作类型 0查询1导出
        //全部规格
        if ($gsid == 0) {
            //导出状态 全部
            if ($isexport == 2) {
                $list = Db::table('card')->column('id,acc,pwdinfo,creat_time,isexport');
            } else {
                $list = Db::table('card')->where('isexport', $isexport)->column('id,acc,pwdinfo,creat_time,isexport');
            }
        } else {
            //导出状态 全部
            if ($isexport == 2) {
                $list = Db::table('card')->where('gsid', $gsid)->column('id,acc,pwdinfo,creat_time,isexport');
            } else {
                $list = Db::table('card')->where('gsid', $gsid)->where('isexport', $isexport)->column('id,acc,pwdinfo,creat_time,isexport');
            }
        }
        $newlist = array();
        foreach ($list as $item) {
            $newacc = '`' . $item['acc'];
            $newpwdinfo = '`' . $item['pwdinfo'];
            $newlist[] = array('id' => $item['id'], 'acc' => $newacc, 'pwdinfo' => $newpwdinfo, 'creat_time' => $item['creat_time'], 'isexport' => $item['isexport']);
        }
        if ($type == 1) {
            foreach ($list as $item) {
                Db::table('card')->where('id', $item['id'])->update(['isexport' => 1]);
            }
        }
        $data = array('status' => 0, 'msg' => '成功', 'data' => $newlist);
        return json($data);
    }

    public function myDCard()
    {
        $did = $_REQUEST['did'];
        $type = $_REQUEST['type'];//0全部 1待激活 2已激活 3已完成
        if ($type == 0) {
            $cdata = Db::view('card', 'acc,type as cardtype')
                ->view('awaitactcard', 'type as acttype', 'card.acc=awaitactcard.acc', 'LEFT')
                ->view('goods_size', 'size as sizename', 'card.gsid=goods_size.id', 'LEFT')
                ->view('goods', 'name as goodsname', 'goods_size.goods_id=goods.id', 'LEFT')
                ->where('card.did', $did)->select();
        } else {
            $cdata = Db::view('card', 'acc,type as cardtype')
                ->view('awaitactcard', 'type as acttype', 'card.acc=awaitactcard.acc', 'LEFT')
                ->view('goods_size', 'size as sizename', 'card.gsid=goods_size.id', 'LEFT')
                ->view('goods', 'name as goodsname', 'goods_size.goods_id=goods.id', 'LEFT')
                ->where('card.did', $did)->where('card.type', $type)->select();
        }
        $data = array('status' => 0, 'msg' => '成功', 'data' => $cdata);
        return json($data);
    }
}