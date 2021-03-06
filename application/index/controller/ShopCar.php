<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/14
 * Time: 13:09
 */

namespace app\index\controller;


use think\Db;

class ShopCar
{
    public function addGoods()
    {
        $did = $_REQUEST['did'];
        $goods_size_id = $_REQUEST['gsid'];
        //判断购物车是否存在该商品
        $shopcardata = Db::table('shopcar')->where('did', $did)->where('goods_size_id', $goods_size_id)->find();
        if ($shopcardata) {
            $shopcarid = $shopcardata['id'];
            $num = $shopcardata['num'];
            $num++;
            Db::table('shopcar')->where('id', $shopcarid)->update(['num' => $num]);
            $data = array('status' => 0, 'msg' => '成功', 'data' => '');
        } else {
            //查询规格的商品id
            $goodssizedata = Db::table('goods_size')->where('id', $goods_size_id)->find();
            if ($goodssizedata) {
                $goods_id = $goodssizedata['goods_id'];
                Db::table('shopcar')->insert(['did' => $did, 'goods_size_id' => $goods_size_id, 'goods_id' => $goods_id, 'num' => 1, 'creat_time' => date("Y-m-d H:i:s", time())]);
                $data = array('status' => 0, 'msg' => '成功', 'data' => '');
            } else {
                $data = array('status' => 1, 'msg' => '该规格不存在', 'data' => '');
            }
        }
        return json($data);
    }

    public function delGoods()
    {
        $did = $_REQUEST['did'];
        $goods_size_id = $_REQUEST['gsid'];
        //判断购物车是否存在该商品
        $shopcardata = Db::table('shopcar')->where('did', $did)->where('goods_size_id', $goods_size_id)->find();
        if ($shopcardata) {
            $shopcarid = $shopcardata['id'];
            Db::table('shopcar')->delete($shopcarid);
            $data = array('status' => 0, 'msg' => '成功', 'data' => '');
        } else {
            $data = array('status' => 1, 'msg' => '该规格不存在', 'data' => '');
        }
        return json($data);
    }

    public function updGoodsNum()
    {
        $did = $_REQUEST['did'];
        $goods_size_id = $_REQUEST['gsid'];
        $type = $_REQUEST['type'];//操作 0减少 1增加
        //判断购物车是否存在该商品
        $shopcardata = Db::table('shopcar')->where('did', $did)->where('goods_size_id', $goods_size_id)->find();
        if ($shopcardata) {
            $shopcarid = $shopcardata['id'];
            $num = $shopcardata['num'];
            switch ($type) {
                case 0:
                    $num--;
                    break;
                case 1:
                    $num++;
                    break;
            }
            if ($num <= 0) {
                Db::table('shopcar')->delete($shopcarid);
                $data = array('status' => 0, 'msg' => '该规格已删除', 'data' => '');
            } else {
                Db::table('shopcar')->where('id', $shopcarid)->update(['num' => $num]);
                $data = array('status' => 0, 'msg' => '成功', 'data' => '');
            }
        } else {
            $data = array('status' => 1, 'msg' => '该规格不存在', 'data' => '');
        }
        return json($data);
    }

    public function showShopCar()
    {
        $did = $_REQUEST['did'];
        //查询代理商折扣
        $ddata = Db::table('distributor')->where('id', $did)->find();
        if ($ddata) {
            $discount = $ddata['discount'];
            $shopcardata = Db::view('shopcar', 'goods_size_id,goods_id,num,ischoose')
                ->view('goods', 'name,headimg', 'shopcar.goods_id=goods.id', 'LEFT')
                ->view('goods_size', 'size', 'shopcar.goods_size_id=goods_size.id', 'LEFT')
                ->where('did', $did)
                ->order('creat_time desc')
                ->select();
            foreach ($shopcardata as $item) {
                $gsdata = Db::table('goods_size')
                    ->where('id', $item['goods_size_id'])
                    ->find();
                $cost = $gsdata['price'] * $discount;
                $returndata[] = array('goods_size_id' => $item['goods_size_id'], 'goods_id' => $item['goods_id'],
                    'num' => $item['num'], 'ischoose' => $item['ischoose'], 'name' => $item['name'], 'headimg' => $item['headimg'],
                    'size' => $item['size'], 'cost' => $cost);
            }
            $data = array('status' => 0, 'msg' => '成功', 'data' => $returndata);
        } else {
            $data = array('status' => 0, 'msg' => '代理商id错误', 'data' => '');
        }
        return json($data);
    }

    public function updGoodsChoose()
    {
        $did = $_REQUEST['did'];
        $goods_size_id = $_REQUEST['gsid'];
        //判断购物车是否存在该商品
        $shopcardata = Db::table('shopcar')->where('did', $did)->where('goods_size_id', $goods_size_id)->find();
        if ($shopcardata) {
            $shopcarid = $shopcardata['id'];
            $ischoose = $shopcardata['ischoose'];
            switch ($ischoose) {
                case 0:
                    $newischoose = 1;
                    break;
                case 1:
                    $newischoose = 0;
                    break;
            }
            Db::table('shopcar')->where('id', $shopcarid)->update(['ischoose' => $newischoose]);
            $data = array('status' => 0, 'msg' => '成功', 'data' => '');
        } else {
            $data = array('status' => 1, 'msg' => '该规格不存在', 'data' => '');
        }
        return json($data);
    }

    public function changeGoodsNum()
    {
        $did = $_REQUEST['did'];
        $goods_size_id = $_REQUEST['gsid'];
        $num = $_REQUEST['num'];
        //判断购物车是否存在该商品
        $shopcardata = Db::table('shopcar')->where('did', $did)->where('goods_size_id', $goods_size_id)->find();
        if ($shopcardata) {
            $shopcarid = $shopcardata['id'];
            if ($num <= 0) {
                Db::table('shopcar')->delete($shopcarid);
                $data = array('status' => 0, 'msg' => '该规格已删除', 'data' => '');
            } else {
                Db::table('shopcar')->where('id', $shopcarid)->update(['num' => $num]);
                $data = array('status' => 0, 'msg' => '成功', 'data' => '');
            }
        } else {
            $data = array('status' => 1, 'msg' => '该规格不存在', 'data' => '');
        }
        return json($data);
    }

    public function delShopCarGoodsSize()
    {
        $did = $_REQUEST['did'];
        $goods_size_id = $_REQUEST['gsid'];

    }
}