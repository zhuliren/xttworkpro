<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\Route;

//Route::rule('路由表达式','路由地址','请求类型','路由参数（数组）','变量规则（数组）');
//用户模块
Route::rule('wxlogin', 'index/User/wxlogin');
Route::rule('login', 'index/User/login');

//商品模块
Route::rule('creatGoods', 'index/Goods/creatGoods');
Route::rule('addGoodsSize', 'index/Goods/addGoodsSize');
Route::rule('updGoodsInfo', 'index/Goods/updGoodsInfo');
Route::rule('updGoodsSize', 'index/Goods/updGoodsSize');
Route::rule('delGoods', 'index/Goods/delGoods');
Route::rule('delGoodsSize', 'index/Goods/delGoodsSize');
Route::rule('getGoosList', 'index/Goods/getGoosList');
Route::rule('getGoodsSize', 'index/Goods/getGoodsSize');
Route::rule('getGoodsListWithSize', 'index/Goods/getGoodsListWithSize');
Route::rule('getGoodsD', 'index/Goods/getGoodsD');

//代理商模块
Route::rule('distributorInfo', 'index/Distributor/distributorInfo');
Route::rule('creatDistributor', 'index/Distributor/creatDistributor');
Route::rule('updDistributorInfo', 'index/Distributor/updDistributorInfo');
Route::rule('updDistributorPwd', 'index/Distributor/updDistributorPwd');
Route::rule('setMyshop', 'index/Distributor/setMyshop');
Route::rule('showMyShop', 'index/Distributor/showMyShop');
Route::rule('getRequstOrderInfo', 'index/Distributor/getRequstOrderInfo');

//代理商购物车模块
Route::rule('addCarGoods', 'index/ShopCar/addGoods');
Route::rule('delCarGoods', 'index/ShopCar/delGoods');
Route::rule('updCarGoodsNum', 'index/ShopCar/updGoodsNum');
Route::rule('showShopCar', 'index/ShopCar/showShopCar');
Route::rule('updCarGoodsChoose', 'index/ShopCar/updGoodsChoose');

//代理商订单模块
Route::rule('creatDOrder', 'index/DOrder/creatDOrder');
Route::rule('myShopOrderList', 'index/DOrder/myShopOrderList');
Route::rule('dOrderDetails', 'index/DOrder/dOrderDetails');
Route::rule('dConfirmOrder', 'index/DOrder/dConfirmOrder');
Route::rule('myDOrderList', 'index/DOrder/myOrderList');

//卡券模块
Route::rule('creatCard', 'index/Card/creatCard');
Route::rule('getCardAccLikeLast', 'index/Card/getCardAccLikeLast');
Route::rule('getCardInfo', 'index/Card/getCardInfo');
Route::rule('bindingCard', 'index/Card/bindingCard');
Route::rule('actCard', 'index/Card/actCard');
Route::rule('userGetCardInfo', 'index/Card/userGetCardInfo');
Route::rule('cardList', 'index/Card/cardList');
Route::rule('cardListAll', 'index/Card/cardListAll');

//用户订单模块
Route::rule('creatOrder', 'index/UserOrder/creatOrder');
Route::rule('myOrderList', 'index/UserOrder/myOrderList');

//代理商授信模块
Route::rule('myLc', 'index/Lc/myLc');
Route::rule('lcHis', 'index/Lc/lcHis');
Route::rule('reLc', 'index/Lc/reLc');
Route::rule('changeLc', 'index/Lc/changeLc');

//后台管理模块
Route::rule('creatRoot', 'index/Root/creatRoot');
Route::rule('rootLogin', 'index/Root/login');
Route::rule('userList', 'index/Root/userList');
Route::rule('dList', 'index/Root/dList');
Route::rule('rootList', 'index/Root/rootList');