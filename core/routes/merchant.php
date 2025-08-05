<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Start Merchant Area
|--------------------------------------------------------------------------
 */

Route::name('merchant.')->group(function () {
    Route::namespace('Merchant\Auth')->group(function () {
        Route::middleware('merchant.guest')->group(function () {
            Route::controller('LoginController')->group(function () {
                Route::get('/', 'showLoginForm')->name('login');
                Route::post('/', 'login');
                Route::get('logout', 'logout')->middleware('merchant')->withoutMiddleware('merchant.guest')->name('logout');
            });

            Route::controller('RegisterController')->group(function () {
                Route::get('register', 'showRegistrationForm')->name('register');
                Route::post('register', 'register')->middleware('registration.status');
                Route::post('check-mail', 'checkUser')->name('checkUser');
            });

            Route::controller('ForgotPasswordController')->group(function () {
                Route::get('password/reset', 'showLinkRequestForm')->name('password.request');
                Route::post('password/email', 'sendResetCodeEmail')->name('password.email');
                Route::get('password/code-verify', 'codeVerify')->name('password.code.verify');
                Route::post('password/verify-code', 'verifyCode')->name('password.verify.code');
            });

            Route::controller('ResetPasswordController')->group(function () {
                Route::post('password/reset', 'reset')->name('password.update');
                Route::get('password/reset/{token}', 'showResetForm')->name('password.reset');
            });
        });
    });

    Route::middleware('merchant')->group(function () {
        Route::namespace('Merchant')->controller('AuthorizationController')->group(function () {
            Route::get('authorization', 'authorizeForm')->name('authorization');
            Route::get('resend-verify/{type}', 'sendVerifyCode')->name('send.verify.code');
            Route::post('verify-email', 'emailVerification')->name('verify.email');
            Route::post('verify-mobile', 'mobileVerification')->name('verify.mobile');
            Route::post('verify-g2fa', 'g2faVerification')->name('2fa.verify');
        });

        Route::namespace('Merchant')->group(function () {
            Route::middleware('check.status:merchant')->group(function () {

                Route::get('user-data', 'MerchantController@userData')->name('data');
                Route::post('user-data-submit', 'MerchantController@userDataSubmit')->name('data.submit');

                Route::middleware('registration.complete:merchant')->group(function () {
                    Route::controller('MerchantController')->group(function () {

                        Route::get('dashboard', 'home')->name('home');
                        Route::get('check/insight', 'checkInsight')->name('check.insight');
                        Route::get('business/api/key', 'apiKey')->name('api.key');
                        Route::post('generate/api/key', 'generateApiKey')->name('generate.key');

                        Route::get('profile-setting', 'profile')->name('profile.setting');
                        Route::post('profile-setting', 'submitProfile');
                        Route::get('change-password', 'changePassword')->name('change.password');
                        Route::post('change-password', 'submitPassword');

                        //2FA
                        Route::get('twofactor', 'show2faForm')->name('twofactor');
                        Route::post('twofactor/enable', 'create2fa')->name('twofactor.enable');
                        Route::post('twofactor/disable', 'disable2fa')->name('twofactor.disable');

                        //All Wallets
                        Route::get('wallets', 'wallets')->name('wallets');

                        //Withdraw

                        //Kyc
                        Route::get('kyc-form', 'kycForm')->name('kyc.form');
                        Route::get('kyc-data', 'kycData')->name('kyc.data');
                        Route::post('kyc-submit', 'kycSubmit')->name('kyc.submit');

                        //QR Code
                        Route::get('/qr-code', 'qrCode')->name('qr');
                        Route::get('/download/qr-code/as/jpg', 'downLoadQrCodeJpg')->name('qr.code.jpg');

                        Route::get('/transaction/history', 'trxHistory')->name('transactions');

                        Route::get('attachment-download/{filHash}', 'attachmentDownload')->name('attachment.download');

                        Route::post('add-device-token', 'addDeviceToken')->name('add.device.token');
                    });

                    Route::controller('MerchantWithdrawController')->middleware('module:withdraw_money')->prefix('withdraw')->name('withdraw')->group(function () {
                        Route::middleware('kyc:merchant')->group(function () {
                            Route::get('/', 'withdraw');

                            Route::get('/methods', 'withdrawMethods')->name('.methods');
                            Route::get('/add-method-page', 'addWithdrawMethodPage')->name('.method.add.page');
                            Route::post('/add-method', 'addWithdrawMethod')->name('.method.add');
                            Route::get('/edit-method/{id}', 'editWithdrawMethod')->name('.edit');
                            Route::post('/method/update', 'withdrawMethodUpdate')->name('.update');

                            Route::post('/money', 'withdrawMoney')->name('.money');
                            Route::get('/preview', 'withdrawPreview')->name('.preview');
                            Route::post('/preview', 'withdrawSubmit')->name('.submit');

                            Route::get('/done', 'withdrawSubmitDone')->name('.submit.done');
                        });

                        Route::get('/history', 'withdrawLog')->name('.history');
                        Route::get('/file-download/{fileHash}', 'fileDownload')->name('.file.download');
                    });

                });

            });
        });
    });
});
