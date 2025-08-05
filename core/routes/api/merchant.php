<?php

use Illuminate\Support\Facades\Route;

Route::name('api.merchant.')->group(function () {

    Route::namespace('Merchant\Auth')->group(function () {
        Route::post('login', 'LoginController@login');
        Route::post('register', 'RegisterController@register');

        Route::controller('ForgotPasswordController')->group(function () {
            Route::post('password/email', 'sendResetCodeEmail')->name('password.email');
            Route::post('password/verify-code', 'verifyCode')->name('password.verify.code');
            Route::post('password/reset', 'reset')->name('password.update');

        });
    });

    Route::middleware(['auth:sanctum', 'ability:merchant'])->group(function () {
        //authorization
        Route::controller('Merchant\AuthorizationController')->group(function () {
            Route::get('authorization', 'authorization')->name('authorization');
            Route::get('resend-verify/{type}', 'sendVerifyCode')->name('send.verify.code');
            Route::post('verify-email', 'emailVerification')->name('verify.email');
            Route::post('verify-mobile', 'mobileVerification')->name('verify.mobile');
            Route::post('verify-g2fa', 'g2faVerification')->name('go2fa.verify');
        });

        Route::middleware(['check.status'])->group(function () {
            Route::post('data-submit', 'Merchant\MerchantController@userDataSubmit')->name('data.submit');

            Route::middleware('registration.complete')->group(function () {
                Route::get('dashboard', 'Merchant\MerchantController@dashboard')->name('dashboard');

                Route::controller('OtpController')->group(function () {
                    Route::post('otp-verify', 'otpVerify')->name('verify.otp.submit');
                    Route::post('otp-resend', 'otpResend')->name('verify.otp.resend');
                });

                Route::get('info', function () {
                    $notify[] = 'User information';
                    return response()->json([
                        'remark'  => 'user_info',
                        'status'  => 'success',
                        'message' => ['success' => $notify],
                        'data'    => [
                            'user' => auth()->user('merchant'),
                        ],
                    ]);
                });

                Route::namespace('Merchant')->group(function () {
                    Route::controller('MerchantController')->group(function () {
                        //KYC
                        Route::get('kyc-form', 'kycForm')->name('kyc.form');
                        Route::post('kyc-submit', 'kycSubmit')->name('kyc.submit');

                        Route::get('transactions', 'transactions')->name('transactions');

                        Route::get('/qr-code', 'qrCode')->name('qr.code');
                        Route::post('/qr-code/download', 'qrCodeDownload')->name('qr.code.download');
                        Route::post('/qr-code/remove', 'qrCodeRemove')->name('qr.code.remove');

                        Route::get('/wallets', 'wallets')->name('wallets');

                        Route::post('profile-setting', 'submitProfile');
                        Route::post('change-password', 'submitPassword');

                        //2FA
                        Route::get('twofactor', 'show2faForm')->name('twofactor');
                        Route::post('twofactor/enable', 'create2fa')->name('twofactor.enable');
                        Route::post('twofactor/disable', 'disable2fa')->name('twofactor.disable');

                        Route::post('add-device-token', 'addDeviceToken');
                        Route::get('push-notifications', 'pushNotifications');
                        Route::post('push-notifications/read/{id}', 'pushNotificationsRead');

                        Route::post('delete-account', 'deleteAccount');
                    });

                    // Withdraw
                    Route::controller('WithdrawController')->prefix('withdraw')->name('withdraw')->group(function () {
                        Route::middleware('kyc')->group(function () {
                            Route::get('/methods', 'withdrawMethods')->name('.methods');
                            Route::get('/add-method', 'addWithdrawMethodPage')->name('.method.add.page');
                            Route::post('/add-method', 'addWithdrawMethod')->name('.method.add');
                            Route::get('/edit-method/{id}', 'editWithdrawMethod')->name('.edit');
                            Route::post('/method/update', 'withdrawMethodUpdate')->name('.update');
                            Route::post('/money', 'withdrawMoney')->name('.money');
                            Route::get('/preview/{trx}', 'withdrawPreview')->name('.preview');
                            Route::post('/money/submit', 'withdrawSubmit')->name('.submit');
                            Route::post('/money/done/{actionId}', 'withdrawSubmitDone')->name('.submit.done');
                        });
                        Route::get('/history', 'withdrawLog')->name('.history');
                    });
                });

            });
        });

        Route::get('logout', 'Merchant\Auth\LoginController@logout');
    });

});
