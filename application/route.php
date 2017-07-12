<?php

use think\Route;
/*Route::get('financial/unbalancedAccount','Financial/unbalancedAccount');
Route::get('financial/tcStatistics','Financial/tcStatistics');
Route::get('financial/rechargeRecord','Financial/rechargeRecord');
Route::any('article/articleadd','Article/articleadd');
Route::get('driver/carstyle','Driver/carstyle');
Route::any('driver/carstyleadd','Driver/carstyleadd');
Route::get('driver/carlength','Driver/carlength');
Route::any('driver/carlengthadd','Driver/carlengthadd');
Route::any('driver/carlengthdel','Driver/carlengthdel');
Route::get('driver/range','Driver/range');*/

return [
    //-------------------
    //  __domain__  域名部署
    //-------------------
    '__domain__' => [
        //'atwwg.api'      => 'api',
        'wztx.oms' => 'admin',
        //'atwwg.spl'      => 'spl'
    ],
    '__rest__' => [
//        'ad' => 'Ad',
//        'driver' => 'Driver',
//        'shipper' => 'Shipper',
//        //'financial' => 'Financial',
//        'withdraw' => 'Withdraw',
//        'share' => 'Share',
//        'order' => 'Order',
//        'orderComment' => 'OrderComment',
//        'articlecustom'=>'ArticleCustom'
    ],
];
