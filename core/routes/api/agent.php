<?php

use Illuminate\Support\Facades\Route;

Route::name('api.agent.')->group(function () {

    Route::namespace('Agent\Auth')->group(function () {
        Route::post('login', 'LoginController@login');
        Route::post('register', 'RegisterController@register');

        Route::controller('ForgotPasswordController')->group(function () {
            Route::post('password/email', 'sendResetCodeEmail')->name('password.email');
            Route::post('password/verify-code', 'verifyCode')->name('password.verify.code');
            Route::post('password/reset', 'reset')->name('password.update');
        });
    });

    Route::middleware(['auth:sanctum', 'ability:agent'])->group(function () {

        //authorization
        Route::controller('Agent\AuthorizationController')->group(function () {
            Route::get('authorization', 'authorization')->name('authorization');
            Route::get('resend-verify/{type}', 'sendVerifyCode')->name('send.verify.code');
            Route::post('verify-email', 'emailVerification')->name('verify.email');
            Route::post('verify-mobile', 'mobileVerification')->name('verify.mobile');
            Route::post('verify-g2fa', 'g2faVerification')->name('go2fa.verify');
        });

        Route::middleware(['check.status'])->group(function () {
            Route::post('data-submit', 'Agent\AgentController@userDataSubmit')->name('data.submit');

            Route::middleware('registration.complete')->group(function () {

                Route::get('dashboard', 'Agent\AgentController@dashboard')->name('dashboard');

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
                            'user' => auth()->user('agent'),
                        ],
                    ]);
                });

                Route::namespace('Agent')->group(function () {
                    Route::controller('AgentController')->group(function () {
                        //KYC
                        Route::get('kyc-form', 'kycForm')->name('kyc.form');
                        Route::post('kyc-submit', 'kycSubmit')->name('kyc.submit');

                        //Report
                        Route::any('deposit/history', 'depositHistory')->name('deposit.history');
                        Route::get('transactions', 'transactions')->name('transactions');

                        Route::get('/qr-code', 'qrCode')->name('qr.code');
                        Route::post('/qr-code/download', 'qrCodeDownload')->name('qr.code.download');
                        Route::post('/qr-code/remove', 'qrCodeRemove')->name('qr.code.remove');

                        Route::get('/wallets', 'wallets')->name('wallets');

                        Route::post('profile-setting', 'submitProfile');
                        Route::post('change-password', 'submitPassword');

                        Route::get('commission-log', 'commissionLog')->name('commission.log');

                        Route::get('twofactor', 'show2faForm');
                        Route::post('twofactor/enable', 'create2fa');
                        Route::post('twofactor/disable', 'disable2fa');

                        Route::post('add-device-token', 'addDeviceToken');
                        Route::get('push-notifications', 'pushNotifications');
                        Route::post('push-notifications/read/{id}', 'pushNotificationsRead');

                        Route::post('delete-account', 'deleteAccount');
                    });

                    //Money In
                    Route::controller('MoneyInController')->middleware('kyc:agent')->group(function () {
                        Route::post('/check-user', 'checkUser')->name('user.check.exist');
                        Route::get('/money-in', 'moneyInForm')->name('money.in');
                        Route::post('/money-in', 'confirmMoneyIn');
                        Route::post('/money-in-done/{actionId}', 'moneyInDone')->name('money.in.done');
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

                // Payment
                Route::controller('PaymentController')->group(function () {
                    Route::get('deposit/methods', 'methods')->name('deposit');
                    Route::post('deposit/insert', 'depositInsert')->name('deposit.insert');
                    Route::get('deposit/confirm', 'depositConfirm')->name('deposit.confirm');
                    Route::get('deposit/manual', 'manualDepositConfirm')->name('deposit.manual.confirm');
                    Route::post('deposit/manual', 'manualDepositUpdate')->name('deposit.manual.update');
                });

            });
        });

        Route::get('logout', 'Agent\Auth\LoginController@logout');
    });

});
