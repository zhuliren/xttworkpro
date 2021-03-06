<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/13
 * Time: 11:25
 */

namespace app\index\controller;


use think\Db;

class Goods
{
    public function newCreatGoods()
    {
        $name = $_REQUEST['name'];//商品名称
        $headimg = $_REQUEST['headimg'];//商品头像
        $introduce = $_REQUEST['introduce'];//商品简介
        $details = $_REQUEST['details'];//商品详情富文本
        $origin = $_REQUEST['origin'];//商品产地
        $isonline = $_REQUEST['isonline'];//是否上线
        $goodsno = $_REQUEST['goodsno'];//产品编号
        //查询是否存在同名产品
        $goodsdata = Db::table('goods')->where('name', $name)->find();
        if ($goodsdata) {
            $goods_id = $goodsdata['id'];
            $name = $goodsdata['name'];
            $headimg = $goodsdata['headimg'];
            $introduce = $goodsdata['introduce'];
            $details = $goodsdata['details'];
            $origin = $goodsdata['origin'];
            $isonline = $goodsdata['isonline'];
            $returndata = array(
                'goodsid' => $goods_id,
                'name' => $name,
                'headimg' => $headimg,
                'introduce' => $introduce,
                'details' => $details,
                'origin' => $origin,
                'isonline' => $isonline
            );
            $data = array('status' => 1, 'msg' => '商品已存在', 'data' => $returndata);
        } else {
            $goods_id = Db::table('goods')
                ->insertGetId(['name' => $name, 'headimg' => $headimg, 'introduce' => $introduce, 'details' => $details, 'origin' => $origin, 'isonline' => $isonline, 'goodsno' => $goodsno]);
            $returndata = array('goodsid' => $goods_id);
            $data = array('status' => 0, 'msg' => '成功', 'data' => $returndata);
        }
        return json($data);
    }

    public function newAddGoodsSize()
    {
        $goods_id = $_REQUEST['goodsid'];//商品id
        $size = $_REQUEST['size'];//商品规格
        $cost = $_REQUEST['cost'];//商品拿货价
        $price = $_REQUEST['price'];//商品建议售价
        $card_price = $_REQUEST['cardprice'];//商品券面价
        $modelid = $_REQUEST['modelid'];//型号
        $size_id = Db::table('goods_size')->insertGetId(['goods_id' => $goods_id, 'size' => $size, 'cost' => $cost, 'price' => $price, 'card_price' => $card_price, 'modelid' => $modelid]);
        Db::table('cardnum')->insert(['gsid' => $size_id]);
        $returndata = array('sizeid' => $size_id, 'goodsid' => $goods_id);
        $data = array('status' => 0, 'msg' => '成功', 'data' => $returndata);
        return json($data);
    }

    public function updGoodsInfo()
    {
        $goods_id = $_REQUEST['goodsid'];
        $name = $_REQUEST['name'];//商品名称
        $headimg = $_REQUEST['headimg'];//商品头像
        $introduce = $_REQUEST['introduce'];//商品简介
        $details = $_REQUEST['details'];//商品详情富文本
        $origin = $_REQUEST['origin'];//商品产地
        $isonline = $_REQUEST['isonline'];//是否上线
        Db::table('goods')
            ->where('id', $goods_id)
            ->update(['name' => $name, 'headimg' => $headimg, 'introduce' => $introduce, 'details' => $details, 'origin' => $origin, 'isonline' => $isonline]);
        $data = array('status' => 0, 'msg' => '成功', 'data' => '');
        return json($data);
    }

    public function updGoodsSize()
    {
        $size_id = $_REQUEST['size_id'];//规格id
        $size = $_REQUEST['size'];//商品规格
        $cost = $_REQUEST['cost'];//商品拿货价
        $price = $_REQUEST['price'];//商品建议售价
        $card_price = $_REQUEST['cardprice'];//商品券面价
        Db::table('goods_size')
            ->where('id', $size_id)
            ->update(['size' => $size, 'cost' => $cost, 'price' => $price, 'card_price' => $card_price]);
        $data = array('status' => 0, 'msg' => '成功', 'data' => '');
        return json($data);
    }

    public function delGoods()
    {
        $goods_id = $_REQUEST['goodsid'];
        Db::table('goods')->delete($goods_id);
        //删除商品规格
        Db::table('goods_size')->where('goods_id', $goods_id)->delete();
        $data = array('status' => 0, 'msg' => '成功', 'data' => '');
        return json($data);
    }

    public function delGoodsSize()
    {
        $size_id = $_REQUEST['size_id'];
        Db::table('goods')->delete($size_id);
        $data = array('status' => 0, 'msg' => '成功', 'data' => '');
        return json($data);
    }

    public function getGoosList()
    {
        $limit = $_REQUEST['limit'];
        $page = $_REQUEST['page'];
        $start = $page * $limit;
        $datanum = Db::table('goods')->count();
        $goodslistdata = Db::table('goods')->limit($start, $limit)
            ->column('id,name,headimg,introduce,origin,isonline');
        $alldata = array('list' => $goodslistdata, 'num' => $datanum);
        $data = array('status' => 0, 'msg' => '成功', 'data' => $alldata);
        return json($data);
    }

    public function getGoodsSize()
    {
        $goods_id = $_REQUEST['goodsid'];
        $goodssizedata = Db::table('goods_size')
            ->where('goods_id', $goods_id)
            ->column('id as sizeid,size,cost,price,card_price');
        $data = array('status' => 0, 'msg' => '成功', 'data' => $goodssizedata);
        return json($data);
    }

    public function getNewGoodsSize()
    {
        $did = $_REQUEST['did'];
        $goods_id = $_REQUEST['goodsid'];
        //查询代理商折扣
        $ddata = Db::table('distributor')->where('id', $did)->find();
        if ($ddata) {
            $discount = $ddata['discount'];
            $goodssizedata = Db::table('goods_size')
                ->where('goods_id', $goods_id)
                ->column('id as sizeid,size,cost,price,card_price');
            $returndata = array();
            foreach ($goodssizedata as $item) {
                $cost = $item['card_price'] * $discount;
                $returndata[] = array('sizeid' => $item['sizeid'], 'size' => $item['size'], 'cost' => $cost, 'price' => $item['price'], 'card_price' => $item['card_price']);
            }
            $data = array('status' => 0, 'msg' => '成功', 'data' => $returndata);
        } else {
            $data = array('status' => 0, 'msg' => '代理商id错误', 'data' => '');
        }
        return json($data);
    }

    public function getGoodsListWithSize()
    {
        $limit = $_REQUEST['limit'];
        $page = $_REQUEST['page'];
        $start = $page * $limit;
        $goodslistdata = Db::table('goods')->limit($start, $limit)
            ->column('id,name,headimg,introduce,origin,isonline');
        $returndata = array();
        foreach ($goodslistdata as $goodslistdatum) {
            $goods_id = $goodslistdatum['id'];
            $goodssizedata = Db::table('goods_size')
                ->where('goods_id', $goods_id)
                ->column('id as sizeid,size,cost,price,card_price');
            $returndata[] = array('goodsinfo' => $goodslistdatum, 'goodssizeinfo' => $goodssizedata);
        }
        $data = array('status' => 0, 'msg' => '成功', 'data' => $returndata);
        return json($data);
    }

    public function getNewGoodsListWithSize()
    {
        $did = $_REQUEST['did'];
        $limit = $_REQUEST['limit'];
        $page = $_REQUEST['page'];
        $start = $page * $limit;
        $goodslistdata = Db::table('goods')->limit($start, $limit)
            ->column('id,name,headimg,introduce,origin,isonline');
        //查询代理商折扣
        $ddata = Db::table('distributor')->where('id', $did)->find();
        if ($ddata) {
            $discount = $ddata['discount'];
            $returndata = array();
            foreach ($goodslistdata as $goodslistdatum) {
                $goods_id = $goodslistdatum['id'];
                $goodssizedata = Db::table('goods_size')
                    ->where('goods_id', $goods_id)
                    ->column('id as sizeid,size,cost,price,card_price');
                $returngoodssizedata = array();
                foreach ($goodssizedata as $item) {
                    $cost = $item['card_price'] * $discount;
                    $returngoodssizedata[] = array('sizeid' => $item['sizeid'], 'size' => $item['size'], 'cost' => $cost, 'price' => $item['price'], 'card_price' => $item['card_price']);
                }
                $returndata[] = array('goodsinfo' => $goodslistdatum, 'goodssizeinfo' => $returngoodssizedata);
            }
            $data = array('status' => 0, 'msg' => '成功', 'data' => $returndata);
        } else {
            $data = array('status' => 0, 'msg' => '代理商id错误', 'data' => '');
        }
        return json($data);
    }

    public function getGoodsD()
    {
        $goods_id = $_REQUEST['goodsid'];
        $goodsdata = Db::table('goods')->where('id', $goods_id)->find();
        if ($goodsdata) {
            $data = array('status' => 0, 'msg' => '成功', 'data' => $goodsdata);
        } else {
            $data = array('status' => 1, 'msg' => '商品不存在', 'data' => '');
        }
        return json($data);
    }
}