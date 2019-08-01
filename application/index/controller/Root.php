<?php


namespace app\index\controller;


use app\index\model\BasicOperation;
use think\Db;

class Root
{
    public function creatRoot()
    {
        $acc = $_REQUEST['acc'];
        $pwd = md5(md5($_REQUEST['pwd']));
        $type = $_REQUEST['type'];//类型 0、超级管理员 1、运维账号 2、财务账号 3、仓管账号
        $rid = Db::table('root')->insertGetId(['acc' => $acc, 'pwd' => $pwd, 'type' => $type]);
        $data = array('status' => 0, 'msg' => '成功', 'data' => array('rid' => $rid));
        return json($data);
    }

    public function login()
    {
        $acc = $_REQUEST['acc'];
        $pwd = md5(md5($_REQUEST['pwd']));
        $rootdata = Db::table('root')->where('acc', $acc)->where('pwd', $pwd)->find();
        if ($rootdata) {
            //匹配菜单
            $menudata = Db::view('rootpathvk', 'roottype')
                ->view('rootpath', 'id,path,name,component,redirect,leaf,menuShow,iconCls', 'rootpathvk.pathid=rootpath.id')
                ->where('rootpathvk.roottype', $rootdata['type'])
                ->select();
            $menu = array();
            //重组数据
            foreach ($menudata as $item) {
                $zpath = Db::table('rootzpath')->where('rootpathid', $item['id'])->column('path,component,name,menuShow');
                $children = array();
                foreach ($zpath as $pathitem) {
                    $children[] = $pathitem;
                }
                $menu[] = array('path' => $item['path'], 'name' => $item['name'], 'component' => $item['component'],
                    'redirect' => $item['redirect'], 'leaf' => $item['leaf'], 'menuShow' => $item['menuShow'],
                    'iconCls' => $item['iconCls'], 'children' => $children);
            }
            $returndata = array('rid' => $rootdata['id'], 'type' => $rootdata['type'], 'menu' => $menu);
            $data = array('status' => 0, 'msg' => '成功', 'data' => $returndata);
        } else {
            $ddata = Db::table('distributor')->where('account', $acc)->where('password', $pwd)->find();
            if ($ddata) {
                $menu = array('商品库', '我的订单', '我的客户', '我的卡券');
                $returndata = array('did' => $ddata['did'], 'name' => $ddata['name'], 'account' => $ddata['account'], 'type' => $ddata['type'], 'grade' => $ddata['grade'], 'menu' => $menu);
                $data = array('status' => 10, 'msg' => '代理商登录', 'data' => $returndata);
            } else {
                $data = array('status' => 1, 'msg' => '请检查账号密码', 'data' => '');
            }
        }
        return json($data);
    }

    public function userList()
    {
        $sort_key = $_REQUEST['sort_key'];
        $operation = new BasicOperation();
        $userdata = Db::table('user')->column('id,name,headimg,creattime');
        if ($userdata) {
            //重组数据
            $returndata = array();
            foreach ($userdata as $item) {
                $ordernum = Db::table('user_order')->where('user_id', $item['id'])->count('id');
                $returndata[] = array('id' => $item['id'], 'name' => $item['name'], 'headimg' => $item['headimg'], 'creattime' => $item['creattime'], 'ordernum' => $ordernum);
            }
            $returndata = $operation->my_sort($returndata, $sort_key, SORT_DESC);
            $data = array('status' => 0, 'msg' => '成功', 'data' => $returndata);
        } else {
            $data = array('status' => 1, 'msg' => '无用户', 'data' => '');
        }
        return json($data);
    }

    public function dList()
    {
        $sort_key = $_REQUEST['sort_key'];
        $operation = new BasicOperation();
        $ddata = Db::table('distributor')->column('id,account,wxid,address,type,grade,name,phone,due,lc,usedlc');
        if ($ddata) {
            $returndata = $operation->my_sort($ddata, $sort_key, SORT_DESC);
            $data = array('status' => 0, 'msg' => '成功', 'data' => $returndata);
        } else {
            $data = array('status' => 1, 'msg' => '无代理商', 'data' => '');
        }
        return json($data);
    }

    public function rootList()
    {
        $rootdata = Db::table('root')->column('id,acc,type');
        if ($rootdata) {
            $data = array('status' => 0, 'msg' => '成功', 'data' => $rootdata);
        } else {
            $data = array('status' => 1, 'msg' => '无后台管理账号', 'data' => '');
        }
        return json($data);
    }

    public function qOrder()
    {
        $rid = $_REQUEST['rid'];
        $order_id = $_REQUEST['orderid'];
        $orderdata = Db::table('order')->where('order_id', $order_id)->find();
        if ($orderdata) {
            if ($orderdata['ordertype'] == 2) {
                Db::table('order')->where('order_id', $order_id)->update(['ordertype' => 3]);
                $data = array('status' => 0, 'msg' => '成功', 'data' => '');
            } else {
                $data = array('status' => 1, 'msg' => '订单状态错误', 'data' => '');
            }
        } else {
            $data = array('status' => 1, 'msg' => '订单号错误', 'data' => '');
        }
        return json($data);
    }

    public function allOrderList()
    {
        $startdate = $_REQUEST['startdate'];
        $enddate = $_REQUEST['enddate'];
        $type = $_REQUEST['type'];//0.全部 1.卡券 2.现货 3.用户提货
        $sort_key = $_REQUEST['sort_key'];
        $limit = $_REQUEST['limit'];
        $page = $_REQUEST['page'];
        $start = $page * $limit;
        $operation = new BasicOperation();
        //判断查看类型
        if ($type == 0) {
            $datanum = Db::table('order')->where('creat_time', 'between time', [$startdate, $enddate])->count();
            $returndata = Db::table('order')->where('creat_time', 'between time', [$startdate, $enddate])
                ->limit($start, $limit)
                ->column('order_id,did,creat_time,paytype,ordertype,payprice,iscard');
            if ($returndata) {
                $returndata = $operation->my_sort($returndata, $sort_key, SORT_DESC);
                $alldata = array('list' => $returndata, 'num' => $datanum);
                $data = array('status' => 0, 'msg' => '成功', 'data' => $alldata);
            } else {
                $data = array('status' => 1, 'msg' => '无订单', 'data' => '');
            }
        } else if ($type == 1) {
            $datanum = Db::table('order')->where('creat_time', 'between time', [$startdate, $enddate])->where('iscard', 0)->count();
            $returndata = Db::table('order')->where('creat_time', 'between time', [$startdate, $enddate])
                ->where('iscard', 0)
                ->limit($start, $limit)
                ->column('order_id,did,creat_time,paytype,ordertype,payprice,iscard');
            if ($returndata) {
                $returndata = $operation->my_sort($returndata, $sort_key, SORT_DESC);
                $alldata = array('list' => $returndata, 'num' => $datanum);
                $data = array('status' => 0, 'msg' => '成功', 'data' => $alldata);
            } else {
                $data = array('status' => 1, 'msg' => '无订单', 'data' => '');
            }
        } else if ($type == 2) {
            $datanum = Db::table('order')->where('creat_time', 'between time', [$startdate, $enddate])->where('iscard', 1)->count();
            $returndata = Db::table('order')->where('creat_time', 'between time', [$startdate, $enddate])
                ->where('iscard', 1)
                ->limit($start, $limit)
                ->column('order_id,did,creat_time,paytype,ordertype,payprice,iscard');
            if ($returndata) {
                $returndata = $operation->my_sort($returndata, $sort_key, SORT_DESC);
                $alldata = array('list' => $returndata, 'num' => $datanum);
                $data = array('status' => 0, 'msg' => '成功', 'data' => $alldata);
            } else {
                $data = array('status' => 1, 'msg' => '无订单', 'data' => '');
            }
        } else if ($type == 3) {
            $datanum = Db::table('user_order')->where('creat_time', 'between time', [$startdate, $enddate])->count();
            $returndata = Db::table('user_order')->where('creat_time', 'between time', [$startdate, $enddate])
                ->limit($start, $limit)
                ->column('user_id,card_id,name,phone,address,creat_time');
            if ($returndata) {
                $returndata = $operation->my_sort($returndata, $sort_key, SORT_DESC);
                $alldata = array('list' => $returndata, 'num' => $datanum);
                $data = array('status' => 0, 'msg' => '成功', 'data' => $alldata);
            } else {
                $data = array('status' => 1, 'msg' => '无订单', 'data' => '');
            }
        }
        return json($data);
    }

    public function awaitActCard()
    {
        $rid = $_REQUEST['rid'];
        $sort = $_REQUEST['sort'];//排序方式 0时间正序 1时间倒叙 2代理商排序 3卡号排序
        $type = $_REQUEST['type'];//0全部 1待激活 2已确认激活
        switch ($sort) {
            case 0:
                $orderby = 'awaitactcard.creat_time asc';
                break;
            case 1:
                $orderby = 'awaitactcard.creat_time desc';
                break;
            case 2:
                $orderby = 'distributor.id asc';
                break;
            case 3:
                $orderby = 'awaitactcard.acc asc';
                break;
        }
        switch ($type) {
            case 1:
                $atype = 0;
                break;
            case 2:
                $atype = 1;
                break;
        }
        if ($type == 0) {
            $adata = Db::view('awaitactcard', 'acc,creat_time,type as acttype')
                ->view('distributor', 'id as did,name,phone', 'awaitactcard.did=distributor.id', 'LEFT')
                ->view('card', 'gsid', 'awaitactcard.acc=card.acc', 'LEFT')
                ->view('goods_size', 'size as sizename', 'card.gsid=goods_size.id', 'LEFT')
                ->order($orderby)->select();
        } else {
            $adata = Db::view('awaitactcard', 'acc,creat_time,type as acttype')
                ->view('distributor', 'id as did,name,phone', 'awaitactcard.did=distributor.id', 'LEFT')
                ->view('card', 'gsid', 'awaitactcard.acc=card.acc', 'LEFT')
                ->view('goods_size', 'size as sizename', 'card.gsid=goods_size.id', 'LEFT')
                ->where('awaitactcard.type', $atype)->order($orderby)->select();
        }
        $data = array('status' => 0, 'msg' => '成功', 'data' => $adata);
        return json($data);
    }

    public function superActCard()
    {
        $rid = $_REQUEST['rid'];
        $did = $_REQUEST['did'];
        $acc = $_REQUEST['acc'];
        //查询卡券信息是否已经激活
        $cdata = Db::table('card')->where('acc', $acc)->find();
        $time = date("Y-m-d H:i:s", time());
        if ($cdata) {
            if ($cdata['type'] == 0) {
                Db::table('card')->where('acc', $acc)->update(['type' => 2, 'did' => $did, 'rid' => $rid, 'binding_time' => $time, 'act_time' => $time]);
                $data = array('status' => 0, 'msg' => '激活成功', 'data' => '');
            } else if ($cdata['type'] == 1) {
                Db::table('card')->where('acc', $acc)->update(['type' => 2, 'act_time' => $time]);
                $data = array('status' => 0, 'msg' => '激活成功', 'data' => '');
            } else {
                $data = array('status' => 1, 'msg' => '卡券已被激活', 'data' => '');
            }
        } else {
            $data = array('status' => 1, 'msg' => '卡券不存在', 'data' => '');
        }
        return json($data);
    }

    public function cardForD()
    {
        $acc = $_REQUEST['acc'];
        $cdata = Db::table('card')->where('acc', $acc)->find();
        if ($cdata) {
            $did = $cdata['did'];
            if ($did) {
                $ddata = Db::table('distributor')->where('id', $did)->find();
                if ($ddata) {
                    $returndata = array('did' => $ddata['id'], 'dname' => $ddata['name']);
                    $data = array('status' => 0, 'msg' => '成功', 'data' => $returndata);
                } else {
                    $data = array('status' => 1, 'msg' => '无代理商', 'data' => '');
                }
            } else {
                $data = array('status' => 1, 'msg' => '无代理商', 'data' => '');
            }
        } else {
            $data = array('status' => 1, 'msg' => '卡券不存在', 'data' => '');
        }
        return json($data);
    }

    public function awaitActCardByOid()
    {
        $rid = $_REQUEST['rid'];
        $sort = $_REQUEST['sort'];//排序方式 0时间正序 1时间倒叙 2代理商排序 3卡号排序
        $type = $_REQUEST['type'];//0全部 1待激活 2已确认激活
        switch ($sort) {
            case 0:
                $orderby = 'awaitactcard.creat_time asc';
                break;
            case 1:
                $orderby = 'awaitactcard.creat_time desc';
                break;
            case 2:
                $orderby = 'distributor.id asc';
                break;
            case 3:
                $orderby = 'awaitactcard.oid asc';
                break;
        }
        switch ($type) {
            case 1:
                $atype = 0;
                break;
            case 2:
                $atype = 1;
                break;
            default:
                $atype = 0;
        }
        if ($type == 0) {
            $adata = Db::view('awaitactcard', 'oid,creat_time,type as acttype')
                ->view('distributor', 'id as did,name,phone', 'awaitactcard.did=distributor.id', 'LEFT')
                ->view('card', 'gsid', 'awaitactcard.acc=card.acc', 'LEFT')
                ->view('goods_size', 'size as sizename,price', 'card.gsid=goods_size.id', 'LEFT')
                ->group('awaitactcard.oid')
                ->order($orderby)->select();
        } else {
            $adata = Db::view('awaitactcard', 'oid,creat_time,type as acttype')
                ->view('distributor', 'id as did,name,phone', 'awaitactcard.did=distributor.id', 'LEFT')
                ->view('card', 'gsid', 'awaitactcard.acc=card.acc', 'LEFT')
                ->view('goods_size', 'size as sizename,price', 'card.gsid=goods_size.id', 'LEFT')
                ->group('awaitactcard.oid')
                ->where('awaitactcard.type', $atype)->order($orderby)->select();
        }
        $return = array();
        foreach ($adata as $item) {
            $cardnumdata = Db::table('awaitactcard')->where('oid', $item['oid'])->column(['count(acc)']);
            $cardnum = $cardnumdata[0];
            $ddata = Db::table('distributor')->where('id', $item['did'])->find();
            $discount = $ddata['discount'];
            $payprice = $discount * $item['price'] * $cardnum;
            $return[] = array('oid' => $item['oid'], 'creat_time' => $item['creat_time'], 'acttype' => $item['acttype'], 'payprice' => $payprice,
                'did' => $item['did'], 'name' => $item['name'], 'phone' => $item['phone'], 'sizename' => $item['sizename'], 'cardnum' => $cardnum);
        }
        $data = array('status' => 0, 'msg' => '成功', 'data' => $return);
        return json($data);
    }

    public function needInvoiceList()
    {
        $rid = $_REQUEST['rid'];
        $odata = Db::view('order', 'id,order_id,payprice,invoicetype,invoiceinfo,confirminvoice')
            ->view('distributor', 'dp,invoicename,bank,bankacc', 'order.did=distributor.id', 'LEFT')
            ->where('invoice', 1)
            ->order('order.confirminvoice asc')
            ->select();
        $data = array('status' => 0, 'msg' => '成功', 'data' => $odata);
        return json($data);
    }

    public function confirmInvoice()
    {
        $rid = $_REQUEST['rid'];
        $order_id = $_REQUEST['order_id'];
        Db::table('order')->where('order_id', $order_id)->update(['confirminvoice' => 1]);
        $data = array('status' => 0, 'msg' => '成功', 'data' => '');
        return json($data);
    }
}