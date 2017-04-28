<?php

Route::group(['middleware' => 'wechat.oauth'], function () {
    Route::get('/', 'ApplicationController@home');
    Route::get('/objects', 'ApplicationController@objects');
    Route::get('/objects/{id}/{period}', 'ApplicationController@objectsDetail');
    Route::get('/orders/hold', 'ApplicationController@ordersHold');
    Route::get('/orders/history', 'ApplicationController@ordersHistory');
    Route::get('/orders/detail/{id}', 'ApplicationController@ordersDetail');
    Route::get('/account', 'ApplicationController@account');
    Route::any('/account/bind', 'ApplicationController@accountBind');
    Route::any('/account/pay', 'ApplicationController@accountPay');
    Route::get('/account/pay/staff', 'ApplicationController@accountPayStaff');
    Route::get('/account/withdraw/records', 'ApplicationController@accountWithdrawRecords');
    Route::any('/account/withdraw', 'ApplicationController@accountWithdraw');
    Route::get('/account/records', 'ApplicationController@accountRecords');
    Route::get('/account/orders', 'ApplicationController@accountOrders');
    Route::get('/support', 'ApplicationController@support');
    Route::get('/support/faq', 'ApplicationController@supportFaq');
    Route::get('/support/service', 'ApplicationController@supportService');
    Route::any('/support/feedback', 'ApplicationController@supportFeedback');
});

Route::get('/account/expand/{id}', 'ApplicationController@accountExpand');

Route::get('/administrator', 'AdministratorController@home');
Route::any('/administrator/signIn', 'AdministratorController@signIn');
Route::get('/administrator/signOut', 'AdministratorController@signOut');
Route::get('/administrator/users', 'AdministratorController@users');
Route::get('/administrator/users/export', 'AdministratorController@usersExport');
Route::get('/administrator/users/{id}/status', 'AdministratorController@statusForUser');
Route::any('/administrator/users/{id}/withhold', 'AdministratorController@withholdForUser');
Route::get('/administrator/orders', 'AdministratorController@orders');
Route::get('/administrator/orders/export', 'AdministratorController@ordersExport');
Route::get('/administrator/records', 'AdministratorController@records');
Route::get('/administrator/records/export', 'AdministratorController@recordsExport');
Route::get('/administrator/payRequests', 'AdministratorController@payRequests');
Route::get('/administrator/payRequests/export', 'AdministratorController@payRequestsExport');
Route::any('/administrator/payRequests/{id}', 'AdministratorController@payForUser');
Route::get('/administrator/withdrawRequests', 'AdministratorController@withdrawRequests');
Route::get('/administrator/withdrawRequests/export', 'AdministratorController@withdrawRequestsExport');
Route::any('/administrator/withdrawRequests/{id}', 'AdministratorController@withdrawForUser');
Route::any('/administrator/withdrawRequests/{id}/cancel', 'AdministratorController@withdrawForUserCanceled');
Route::get('/administrator/objects', 'AdministratorController@objects');
Route::get('/administrator/feedbacks', 'AdministratorController@feedbacks');
Route::get('/administrator/administrators', 'AdministratorController@administrators');
Route::get('/administrator/orderControl', 'AdministratorController@orderControl');
Route::get('/administrator/orderWillWin', 'AdministratorController@orderWillWin');
Route::get('/administrator/orderWillLost', 'AdministratorController@orderWillLost');

Route::get('/api/update', 'ApiController@update');
Route::get('/api/objects', 'ApiController@objects');
Route::get('/api/objects/{id}/{period}', 'ApiController@objectsDetail');
Route::get('/api/objects/{id}/{period}/update', 'ApiController@objectsDetailUpdate');
Route::get('/api/orders/{id}', 'ApiController@ordersDetail');
Route::get('/api/fetch', 'ApiController@fetch');
Route::get('/api/compute', 'ApiController@compute');

Route::post('/api/captcha', 'ApiController@captchaCreate');
Route::post('/api/order', 'ApiController@orderCreate');
Route::get('/api/pay/{id}', 'ApiController@payRequestUpdate');

Route::any('/callbacks/wechat', 'CallbackController@listenToWechat');
Route::any('/callbacks/payments/yunpay/notify', 'CallbackController@listenToYunpay');
Route::any('/callbacks/payments/yunpay/return', 'CallbackController@listenToYunpayReturn');

Route::get('/test', 'TestController@run');