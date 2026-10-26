<?php
use think\facade\Route;
use app\middleware\TokenAuth;

Route::group('api', function () {

// ============ 公开接口 ============
Route::post('sms/send', 'Sms/send');
Route::post('user/register', 'User/register');
Route::post('user/login', 'User/login');
Route::post('user/mobilelogin', 'User/mobilelogin');
Route::post('user/resetpwd', 'User/resetpwd');
    Route::post('user/sendsms', 'User/sendSms');
    Route::post('user/realName', 'User/realName');
Route::post('index/getConfig', 'Index/getConfig');
Route::get('site/settings', 'Index/siteSettings');
Route::post('site/settings', 'Index/siteSettings');
Route::post('banner/show/getBanner', 'Index/getBanner');
Route::post('index/afficheList', 'Index/afficheList');
Route::post('index/affichetypelist', 'Index/affichetypelist');
Route::get('afficheDetail/ids/:id', 'Index/afficheDetail');
Route::post('afficheDetail/ids/:id', 'Index/afficheDetail');
Route::post('discover/ant_news/category', 'Index/newsCategory');
Route::post('discover/ant_news/list', 'Index/newsList');
Route::post('index/getSeries', 'Index/getSeries');
Route::get('index/customSeriesList', 'Index/customSeriesList');
Route::post('index/customSeriesList', 'Index/customSeriesList');
Route::post('box/blind_box/list', 'Blindbox/list');
Route::post('box/blind_box/BoxDetail/ids/:id', 'Blindbox/boxDetail');

// ============ 需登录 ============
Route::group(function () {
    Route::post('user/getUserInfo', 'User/getUserInfo');
    Route::post('user/profile', 'User/profile');
    Route::post('user/setPayPassword', 'User/setPayPassword');
    Route::post('user/checkPaypass', 'User/checkPaypass');
    Route::post('user/realname_submit', 'User/realnameSubmit');
    Route::post('user/ranking', 'User/ranking');
    Route::get('user/invite_info', 'User/inviteInfo');
    Route::post('user/invite_info', 'User/inviteInfo');
    Route::post('user/invite_records', 'User/inviteRecords');
    Route::post('user/destroy', 'User/destroy');
    Route::post('wallet/wallet', 'Wallet/wallet');
    Route::post('wallet/transactions', 'Wallet/transactions');
    Route::post('user/address_list', 'Address/list');
    Route::post('user/edit_address', 'Address/edit');
    Route::post('user/delUserAddress', 'Address/del');
    Route::post('user/address_detail', 'Address/detail');
    Route::post('market/market/getMarketList', 'Collectible/getMarketList');
    Route::post('market/market/marketrebang', 'Collectible/marketrebang');
    Route::post('market/market/marketGoodsDetail', 'Collectible/marketGoodsDetail');
    Route::post('box/collection/ProductDetail/ids/:id', 'Collectible/productDetail');
    Route::post('box/collection/ProductDetail/ids', 'Collectible/productDetail');
    Route::post('user_collection/favorite', 'Collectible/favorite');
    Route::post('market/market/getMarketGoodsListByGoodsId', 'Market/getMarketGoodsListByGoodsId');
    Route::post('market/market/MarketDetail/ids/:id', 'Market/marketDetail');
    Route::post('market/market/MarketDetail/ids', 'Market/marketDetail');
    Route::post('market/market/createOrder', 'Market/createOrder');
    Route::post('market/market/consign', 'Market/consign');
    Route::post('market/trade/createBegBuyOrder', 'Market/createBegBuyOrder');
    Route::post('market/trade/begBuyOrderList', 'Market/begBuyOrderList');
    Route::post('market/trade/getSellCollectionInfo', 'Market/getSellCollectionInfo');
    Route::post('market/trade/tradingInformation', 'Market/tradingInformation');
    Route::post('market/Prepayment/myOrderlist', 'Market/myOrderlist');
    Route::post('market/Prepayment/collectionDetail', 'Market/prepayCollectionDetail');
    Route::post('market/Prepayment/createOrder', 'Market/batchPayOrder');
    Route::post('order/order/myConsignment', 'Market/myConsignment');
    Route::post('payment/mock/notify', 'Market/mockNotify');
    Route::post('order/order/orderList', 'Order/orderList');
    Route::post('order/order/MarketOrderDetail/ids/:id', 'Order/marketOrderDetail');
    Route::post('order/order/MarketOrderDetail/ids', 'Order/marketOrderDetail');
    Route::post('order/order/orderDetail/ids/:id', 'Order/orderDetail');
    Route::post('order/order/orderDetail/ids', 'Order/orderDetail');
    Route::post('order/order/saleMarketOrderDetail', 'Order/saleMarketOrderDetail');
    Route::post('order/order/payOrder', 'Order/payOrder');
    Route::post('order/order/cancelOrder', 'Order/cancelOrder');
    Route::post('order/createVIPOrder', 'Order/createVIPOrder');
    Route::post('order/order/giveOrderDetail/ids/:id', 'Order/giveOrderDetail');
    Route::post('user_collection/user_collection/antMycollection', 'Mycollection/antMycollection');
    Route::post('user_collection/user_collection/detail/ids/:id', 'Mycollection/detail');
    Route::post('user_collection/user_collection/detail/ids', 'Mycollection/detail');
    Route::post('box/blind_box/buy', 'Blindbox/buy');
    Route::post('user_collection/user_collection/openbox', 'Blindbox/openbox');
    Route::post('user_collection/user_collection/openBoxs', 'Blindbox/openBoxs');
    Route::post('box/blind_box/openlog', 'Blindbox/openlog');
    Route::post('synthesis/themeList', 'Synthesis/themeList');
    Route::post('synthesis/themeinfo', 'Synthesis/themeinfo');
    Route::post('synthesis/playInfo', 'Synthesis/playInfo');
    Route::post('synthesis/selectCollection', 'Synthesis/selectCollection');
    Route::post('synthesis/synthesizeNow', 'Synthesis/synthesizeNow');
    Route::post('synthesis/recordList', 'Synthesis/recordList');
    Route::post('synthesis/recordInfo', 'Synthesis/recordInfo');
    Route::post('addons/signin/api.index/index', 'Activity/signIndex');
    Route::post('addons/signin/api.index/doSign', 'Activity/doSign');
    Route::post('addons/signin/api.index/fillup', 'Activity/fillup');
    Route::post('addons/signin/api.index/monthSign', 'Activity/signIndex');
    Route::post('addons/signin/api.index/sign', 'Activity/doSign');
    Route::post('lottery/info', 'Activity/lotteryInfo');
    Route::post('lottery/draw', 'Activity/draw');
    Route::post('lottery/statistics', 'Activity/lotteryStatistics');
    Route::post('lottery/drawRecord', 'Activity/drawRecord');
    Route::post('draw/get', 'Activity/lotteryInfo');
    Route::post('draw/drawRecord', 'Activity/drawRecord');
    Route::post('draw/drawLog', 'Activity/drawRecord');
    Route::post('crystal_goods/list', 'Activity/crystalList');
    Route::post('crystal_goods/crystalDetail', 'Activity/crystalDetail');
    Route::post('crystal_goods/exchange', 'Activity/exchange');
    Route::post('crystal_goods/exchangeRecord', 'Activity/exchangeRecord');
    Route::get('airdrop/activities', 'Activity/airdropActivities');
    Route::post('airdrop/activities', 'Activity/airdropActivities');
    Route::get('airdrop/records', 'Activity/airdropRecords');
    Route::post('airdrop/records', 'Activity/airdropRecords');
    Route::post('airdrop/eligibilities', 'Activity/airdropEligibilities');
    Route::post('user_collection/user_collection/getRecord', 'Activity/flowRecord');
    Route::post('user/commision_info', 'Activity/commisionInfo');
    Route::post('user/commison_rank', 'Activity/commisonRank');
    Route::post('user/bei_yao_list', 'Activity/beiYaoList');
    Route::post('Activity/category', 'Activity/activityCategory');
    Route::post('activity/index', 'Activity/activityIndex');
}, )->middleware(TokenAuth::class);

});

// 兜底：未匹配接口返回源站风格 404
Route::miss(function () {
    return json(['code' => 404, 'msg' => '接口不存在', 'time' => (string)time(), 'data' => null]);
});
