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

//代理商模块
Route::rule('distributorInfo', 'index/Distributor/distributorInfo');
Route::rule('creatDistributor', 'index/Distributor/creatDistributor');
Route::rule('updDistributorInfo', 'index/Distributor/updDistributorInfo');
Route::rule('updDistributorPwd', 'index/Distributor/updDistributorPwd');
Route::rule('setMyshop', 'index/Distributor/setMyshop');
Route::rule('showMyShop', 'index/Distributor/showMyShop');

//代理商购物车模块
Route::rule('addGoods', 'index/ShopCar/addGoods');
Route::rule('updGoodsNum', 'index/ShopCar/updGoodsNum');

