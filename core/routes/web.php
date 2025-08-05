<?php

use Illuminate\Support\Facades\Route;

Route::get('/clear', function(){
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');
});


Route::get('cron', 'CronController@cron')->name('cron');

// User Support Ticket
Route::controller('TicketController')->prefix('ticket')->name('ticket.')->group(function () {
    Route::get('/', 'supportTicket')->name('index');
    Route::get('new', 'openSupportTicket')->name('open');
    Route::post('create', 'storeSupportTicket')->name('store');
    Route::get('view/{ticket}', 'viewTicket')->name('view');
    Route::post('reply/{id}', 'replyTicket')->name('reply');
    Route::post('close/{id}', 'closeTicket')->name('close');
    Route::get('download/{attachment_id}', 'ticketDownload')->name('download');
});

Route::controller('OtpController')->group(function () {
    Route::get('otp-verification', 'otpVerification')->name('verify.otp');
    Route::get('otp-resend', 'otpResend')->name('verify.otp.resend');
    Route::post('otp-verify', 'otpVerify')->name('verify.otp.submit');
});

Route::get('app/deposit/confirm/{hash}', 'Gateway\PaymentController@appDepositConfirm')->name('deposit.app.confirm');

Route::controller('SiteController')->group(function () {
    Route::get('/session/status', 'sessionStatus')->name('session.status');
    Route::get('/login', 'login')->name('login'); 

    Route::get('/invoice/payment/{invoiceNum}', 'invoicePayment')->name('invoice.payment');
    Route::get('/api/documentation', 'apiDocumentation')->name('api.documentation');


    Route::post('subscribe', 'subscribe')->name('subscribe');

    
    Route::get('/contact', 'contact')->name('contact');
    Route::post('/contact', 'contactSubmit');
    Route::get('/change/{lang?}', 'changeLanguage')->name('lang');

    Route::get('cookie-policy', 'cookiePolicy')->name('cookie.policy');

    Route::get('/cookie/accept', 'cookieAccept')->name('cookie.accept');

    Route::get('/announces', 'blogs')->name('blogs');
    Route::get('announce/{slug}', 'blogDetails')->name('blog.details');

    Route::get('policy/{slug}', 'policyPages')->name('policy.pages');

    Route::get('placeholder-image/{size}', 'placeholderImage')->withoutMiddleware('maintenance')->name('placeholder.image');
    Route::get('maintenance-mode','maintenance')->withoutMiddleware('maintenance')->name('maintenance');

    Route::get('qr/scan/{uniqueCode}','SiteController@qrScan')->name('qr.scan');

    Route::get('/{slug}', 'pages')->name('pages');
    Route::get('/', 'index')->name('home');
});


//Live payment
Route::controller('GetPaymentController')->group(function () {
    Route::match(['get','post'],'/payment/initiate', 'initiatePayment')->name('initiate.payment');
    Route::get('initiate/payment/checkout', 'initiatePaymentAuthView')->name('initiate.payment.auth.view');
    Route::post('initiate/payment/check-mail', 'checkEmail')->name('payment.check.email');
    Route::get('verify/payment', 'verifyPayment')->name('payment.verify');
    Route::post('confirm/payment', 'verifyPaymentConfirm')->name('confirm.payment');
    Route::get('resend/verify/code', 'sendVerifyCode')->name('resend.code');
    Route::get('cancel/payment', 'cancelPayment')->name('cancel.payment');
});

//Test payment
Route::controller('TestPaymentController')->prefix('sandbox')->name('test.')->group(function () {
    Route::match(['get','post'],'/payment/initiate', 'initiatePayment')->name('initiate.payment');
    Route::get('initiate/payment/checkout', 'initiatePaymentAuthView')->name('initiate.payment.auth.view');
    Route::post('initiate/payment/check-mail', 'checkEmail')->name('payment.check.email');
    Route::get('verify/payment', 'verifyPayment')->name('payment.verify');
    Route::post('confirm/payment', 'verifyPaymentConfirm')->name('confirm.payment');
    Route::get('cancel/payment', 'cancelPayment')->name('cancel.payment');
});
