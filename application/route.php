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
Route::rule('intoUserInfo', 'index/User/intoUserInfo');

//商品模块
Route::rule('updGoodsInfo', 'index/Goods/updGoodsInfo');
Route::rule('updGoodsSize', 'index/Goods/updGoodsSize');
Route::rule('delGoods', 'index/Goods/delGoods');
Route::rule('delGoodsSize', 'index/Goods/delGoodsSize');
Route::rule('getGoosList', 'index/Goods/getGoosList');
Route::rule('getGoodsSize', 'index/Goods/getGoodsSize');
Route::rule('getGoodsListWithSize', 'index/Goods/getGoodsListWithSize');
Route::rule('getGoodsD', 'index/Goods/getGoodsD');
Route::rule('newCreatGoods', 'index/Goods/newCreatGoods');
Route::rule('newAddGoodsSize', 'index/Goods/newAddGoodsSize');
Route::rule('getNewGoodsSize', 'index/Goods/getNewGoodsSize');
Route::rule('getNewGoodsListWithSize', 'index/Goods/getNewGoodsListWithSize');

//代理商模块
Route::rule('distributorInfo', 'index/Distributor/distributorInfo');
Route::rule('creatDistributor', 'index/Distributor/creatDistributor');
Route::rule('updDistributorInfo', 'index/Distributor/updDistributorInfo');
Route::rule('updDistributorPwd', 'index/Distributor/updDistributorPwd');
Route::rule('setMyshop', 'index/Distributor/setMyshop');
Route::rule('showMyShop', 'index/Distributor/showMyShop');
Route::rule('getRequstOrderInfo', 'index/Distributor/getRequstOrderInfo');
Route::rule('addBalance', 'index/Distributor/addBalance');
Route::rule('showBalance', 'index/Distributor/showBalance');
Route::rule('changePwd', 'index/Distributor/changePwd');
Route::rule('useCard', 'index/Distributor/useCard');
Route::rule('myWallet', 'index/Distributor/myWallet');
Route::rule('showInvoiceInfo', 'index/Distributor/showInvoiceInfo');
Route::rule('logOff', 'index/Distributor/logOff');
Route::rule('dInfo', 'index/Distributor/dInfo');
Route::rule('resetDPwd', 'index/Distributor/resetDPwd');
Route::rule('DForRegion', 'index/Distributor/DForRegion');
Route::rule('nowRegion', 'index/Distributor/nowRegion');
Route::rule('bindingRegion', 'index/Distributor/bindingRegion');
Route::rule('newDList', 'index/Distributor/dList');


//代理商购物车模块
Route::rule('addCarGoods', 'index/ShopCar/addGoods');
Route::rule('delCarGoods', 'index/ShopCar/delGoods');
Route::rule('updCarGoodsNum', 'index/ShopCar/updGoodsNum');
Route::rule('showShopCar', 'index/ShopCar/showShopCar');
Route::rule('updCarGoodsChoose', 'index/ShopCar/updGoodsChoose');
Route::rule('changeGoodsNum', 'index/ShopCar/changeGoodsNum');

//代理商订单模块
Route::rule('myShopOrderList', 'index/DOrder/myShopOrderList');
Route::rule('dOrderDetails', 'index/DOrder/dOrderDetails');
Route::rule('dConfirmOrder', 'index/DOrder/dConfirmOrder');
Route::rule('myDOrderList', 'index/DOrder/myOrderList');
Route::rule('noConfirmedDOrder', 'index/DOrder/noConfirmedDOrder');
Route::rule('noDepositDOrder', 'index/DOrder/noDepositDOrder');
Route::rule('depositDOrder', 'index/DOrder/depositDOrder');
Route::rule('dOrderComplete', 'index/DOrder/dOrderComplete');
Route::rule('creatDOrder', 'index/DOrder/creatDOrder');

//卡券模块
Route::rule('creatCard', 'index/Card/creatCard');
Route::rule('getCardAccLikeLast', 'index/Card/getCardAccLikeLast');
Route::rule('getCardInfo', 'index/Card/getCardInfo');
Route::rule('bindingCard', 'index/Card/bindingCard');
Route::rule('actCard', 'index/Card/actCard');
Route::rule('userGetCardInfo', 'index/Card/userGetCardInfo');
Route::rule('cardListAll', 'index/Card/cardListAll');
Route::rule('actCardLot', 'index/Card/actCardLot');
Route::rule('actCardByRid', 'index/Card/actCardByRid');
Route::rule('bindingCardLot', 'index/Card/bindingCardLot');
Route::rule('newCreatCard', 'index/Card/newCreatCard');
Route::rule('getCardType', 'index/Card/getCardType');
Route::rule('setCardType', 'index/Card/setCardType');
Route::rule('delCardType', 'index/Card/delCardType');
Route::rule('getCardAllInfo', 'index/Card/getCardAllInfo');
Route::rule('creatCardByNum', 'index/Card/creatCardByNum');
Route::rule('selCardPwdInfo', 'index/Card/selCardPwdInfo');
Route::rule('myDCard', 'index/Card/myDCard');
Route::rule('actCardByRidFromOid', 'index/Card/actCardByRidFromOid');

//用户订单模块
Route::rule('creatOrder', 'index/UserOrder/creatOrder');
Route::rule('myOrderList', 'index/UserOrder/myOrderList');
Route::rule('userOrderList', 'index/UserOrder/userOrderList');
Route::rule('userOrderComplete', 'index/UserOrder/userOrderComplete');

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
Route::rule('qOrder', 'index/Root/qOrder');
Route::rule('allOrderList', 'index/Root/allOrderList');
Route::rule('awaitActCard', 'index/Root/awaitActCard');
Route::rule('awaitActCardByOid', 'index/Root/awaitActCardByOid');
Route::rule('superActCard', 'index/Root/superActCard');
Route::rule('cardForD', 'index/Root/cardForD');
Route::rule('needInvoiceList', 'index/Root/needInvoiceList');
Route::rule('confirmInvoice', 'index/Root/confirmInvoice');