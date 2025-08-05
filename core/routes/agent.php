<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Start Agent Area
|--------------------------------------------------------------------------
 */

Route::name('agent.')->group(function () {
    Route::namespace('Agent\Auth')->group(function () {
        Route::middleware('agent.guest')->group(function () {
            Route::controller('LoginController')->group(function () {
                Route::get('/', 'showLoginForm')->name('login');
                Route::post('/', 'login');
                Route::get('logout', 'logout')->middleware('agent')->withoutMiddleware('agent.guest')->name('logout');
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

    Route::middleware('agent')->group(function () {
        Route::namespace('Agent')->controller('AuthorizationController')->group(function () {
            Route::get('authorization', 'authorizeForm')->name('authorization');
            Route::get('resend-verify/{type}', 'sendVerifyCode')->name('send.verify.code');
            Route::post('verify-email', 'emailVerification')->name('verify.email');
            Route::post('verify-mobile', 'mobileVerification')->name('verify.mobile');
            Route::post('verify-g2fa', 'g2faVerification')->name('2fa.verify');
        });

        Route::namespace('Agent')->group(function () {
            Route::middleware('check.status:agent')->group(function () {

                Route::get('user-data', 'AgentController@userData')->name('data');
                Route::post('user-data-submit', 'AgentController@userDataSubmit')->name('data.submit');

                Route::middleware('registration.complete:agent')->group(function () {

                    Route::controller('AgentController')->group(function () {
                        Route::get('dashboard', 'home')->name('home');
                        Route::get('check/insight', 'checkInsight')->name('check.insight');

                        Route::get('profile-setting', 'profile')->name('profile.setting');
                        Route::post('profile-setting', 'submitProfile');
                        Route::get('change-password', 'changePassword')->name('change.password');
                        Route::post('change-password', 'submitPassword');

                        //2FA
                        Route::get('twofactor', 'show2faForm')->name('twofactor');
                        Route::post('twofactor/enable', 'create2fa')->name('twofactor.enable');
                        Route::post('twofactor/disable', 'disable2fa')->name('twofactor.disable');

                        //More Wallets
                        Route::get('wallets', 'wallets')->name('wallets');

                        //Kyc
                        Route::get('kyc-form', 'kycForm')->name('kyc.form');
                        Route::get('kyc-data', 'kycData')->name('kyc.data');
                        Route::post('kyc-submit', 'kycSubmit')->name('kyc.submit');

                        //QR Code
                        Route::get('/qr-code', 'qrCode')->name('qr');
                        Route::get('/download/qr-code/as/jpg', 'downLoadQrCodeJpg')->name('qr.code.jpg');

                        //Trx History
                        Route::get('/commission-log', 'commissionLog')->name('commission.log');
                        Route::get('/transaction/history', 'trxHistory')->name('transactions');
                        Route::get('/add-money/history', 'depositHistory')->name('deposit.history')->middleware('module:withdraw_money');

                        Route::post('add-device-token', 'addDeviceToken')->name('add.device.token');

                        Route::get('attachment-download/{filHash}', 'attachmentDownload')->name('attachment.download');
                    });

                    //Money In
                    Route::controller('MoneyInController')->middleware('kyc:agent')->group(function () {
                        Route::post('/user/check', 'checkUser')->name('user.check.exist');
                        Route::get('/money-in', 'moneyInForm')->name('money.in');
                        Route::post('/money-in', 'confirmMoneyIn');
                        Route::get('/money-in-done', 'moneyInDone')->name('money.in.done');
                    });

                    // Withdraw
                    Route::controller('AgentWithdrawController')->prefix('withdraw')->name('withdraw')->group(function () {
                        Route::middleware('kyc:agent')->group(function () {
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

        //Deposit
        Route::middleware('registration.complete:agent')->controller('Gateway\PaymentController')->group(function () {
            Route::middleware(['agent', 'module:add_money'])->controller('Gateway\PaymentController')->group(function () {
                Route::any('/payment', 'deposit')->name('deposit');
                Route::post('payment/insert', 'depositInsert')->name('deposit.insert');
                Route::get('payment/preview', 'depositPreview')->name('deposit.preview');
                Route::get('payment/confirm', 'depositConfirm')->name('deposit.confirm');
                Route::get('payment/manual', 'manualDepositConfirm')->name('deposit.manual.confirm');
                Route::post('payment/manual', 'manualDepositUpdate')->name('deposit.manual.update');
            });
        });

    });
});
