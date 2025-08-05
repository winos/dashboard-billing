<?php

namespace App\Http\Controllers\Api\User;

use App\Constants\Status;
use App\Http\Controllers\Api\Common;
use App\Http\Controllers\Controller;
use App\Lib\FormProcessor;
use App\Lib\GoogleAuthenticator;
use App\Models\DeviceToken;
use App\Models\Form;
use App\Models\GeneralSetting;
use App\Models\NotificationLog;
use App\Models\QRcode;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Rules\FileTypeValidate;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class UserController extends Controller
{
    use Common;

    public function dashboard()
    {
        $user = auth()->user();

        $wallets = $user->topTransactedWallets()->with('currency');

        $totalBalance[] = 0;
        foreach ($wallets->get() as $wallet) {
            $totalBalance[] = $wallet->balance * $wallet->currency->rate;
        }

        $wallets = $wallets->take(3)->get();

        $latestTrx = Transaction::where('user_id', $user->id)->where('user_type', 'USER')
            ->with('currency', 'receiverUser', 'receiverAgent', 'receiverMerchant')->orderBy('id', 'desc')->take(10)
            ->get();

        $totalMoneyInOut = $user->moneyInOut();
        $general         = GeneralSetting::first();

        $notify[] = 'Dashboard';
        return response()->json([
            'remark'  => 'dashboard',
            'status'  => 'success',
            'message' => ['success' => $notify],
            'data'    => [
                'user'                    => $user,
                'wallets'                 => $wallets,
                'latest_trx'              => $latestTrx,
                'last_7_day_money_in_out' => $totalMoneyInOut,
                'total_site_balance'      => showAmount(array_sum(@$totalBalance), $general->currency, currencyFormat:false),
            ],
        ]);
    }

    public function userDataSubmit(Request $request)
    {
        $user = auth()->user();
        if ($user->profile_complete == Status::YES) {
            $notify[] = 'You\'ve already completed your profile';
            return response()->json([
                'remark'  => 'already_completed',
                'status'  => 'error',
                'message' => ['error' => $notify],
            ]);
        }

        $countryData  = (array) json_decode(file_get_contents(resource_path('views/partials/country.json')));
        $countryCodes = implode(',', array_keys($countryData));
        $mobileCodes  = implode(',', array_column($countryData, 'dial_code'));
        $countries    = implode(',', array_column($countryData, 'country'));

        $validator = Validator::make($request->all(), [
            'country_code' => 'required|in:' . $countryCodes,
            'country'      => 'required|in:' . $countries,
            'mobile_code'  => 'required|in:' . $mobileCodes,
            'username'     => 'required|unique:users|min:6',
            'mobile'       => ['required', 'regex:/^([0-9]*)$/', Rule::unique('users')->where('dial_code', $request->mobile_code)],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => $validator->errors()->all()],
            ]);
        }

        if (preg_match("/[^a-z0-9_]/", trim($request->username))) {
            $notify[] = 'No special character, space or capital letters in username';
            return response()->json([
                'remark' => 'validation_error',
                'status' => 'error',
                'message' => ['error' => $notify],
            ]);
        }

        $user->country_code = $request->country_code;
        $user->mobile       = $request->mobile;
        $user->username     = $request->username;

        $user->address      = $request->address;
        $user->city         = $request->city;
        $user->state        = $request->state;
        $user->zip          = $request->zip;
        $user->country_name = @$request->country;
        $user->dial_code    = $request->mobile_code;

        $user->profile_complete = Status::YES;
        $user->save();

        $notify[] = 'Profile completed successfully';
        return response()->json([
            'remark'  => 'profile_completed',
            'status'  => 'success',
            'message' => ['success' => $notify],
            'data'    => [
                'user' => $user,
            ],
        ]);
    }

    public function kycForm()
    {
        if (auth()->user()->kv == Status::KYC_PENDING) {
            $notify[] = 'Your KYC is under review';
            return response()->json([
                'remark'  => 'under_review',
                'status'  => 'error',
                'message' => ['error' => $notify],
            ]);
        }
        if (auth()->user()->kv == Status::KYC_VERIFIED) {
            $notify[] = 'You are already KYC verified';
            return response()->json([
                'remark'  => 'already_verified',
                'status'  => 'error',
                'message' => ['error' => $notify],
            ]);
        }
        $form     = Form::where('act', 'user_kyc')->first();
        $notify[] = 'KYC field is below';
        return response()->json([
            'remark'  => 'kyc_form',
            'status'  => 'success',
            'message' => ['success' => $notify],
            'data'    => [
                'form' => @$form->form_data,
            ],
        ]);
    }

    public function kycSubmit(Request $request)
    {
        $form = Form::where('act', 'user_kyc')->first();
        if (!$form) {
            $notify[] = 'Invalid KYC request';
            return response()->json([
                'remark'  => 'invalid_request',
                'status'  => 'error',
                'message' => ['error' => $notify],
            ]);
        }
        $formData       = $form->form_data;
        $formProcessor  = new FormProcessor();
        $validationRule = $formProcessor->valueValidation($formData);

        $validator = Validator::make($request->all(), $validationRule);

        if ($validator->fails()) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => $validator->errors()->all()],
            ]);
        }
        $user = auth()->user();
        foreach (@$user->kyc_data ?? [] as $kycData) {
            if ($kycData->type == 'file') {
                fileManager()->removeFile(getFilePath('verify') . '/' . $kycData->value);
            }
        }
        $userData = $formProcessor->processFormData($request, $formData);

        $user->kyc_data             = $userData;
        $user->kyc_rejection_reason = null;
        $user->kv                   = Status::KYC_PENDING;
        $user->save();

        $notify[] = 'KYC data submitted successfully';
        return response()->json([
            'remark'  => 'kyc_submitted',
            'status'  => 'success',
            'message' => ['success' => $notify],
        ]);

    }

    public function depositHistory()
    {
        $deposits = auth()->user()->deposits()->searchable(['trx'])->with(['gateway'])->orderBy('id', 'desc')->with('currency')->apiQuery();

        $notify[] = 'Deposit data';
        return response()->json([
            'remark'  => 'deposits',
            'status'  => 'success',
            'message' => ['success' => $notify],
            'data'    => [
                'deposits' => $deposits,
            ],
        ]);
    }

    public function transactions(Request $request)
    {
        $transactions = $this->trxLog($request);

        $operations = ['add_money', 'money_out', 'transfer_money', 'request_money', 'make_payment', 'exchange_money', 'create_voucher', 'withdraw', 'balance_add', 'balance_subtract'];

        $times = ['7days', '15days', '1month', '1year'];

        $allCurrency = auth()->user()->wallets;
        $currencies  = [];

        foreach ($allCurrency as $currency) {
            $currencies[] = $currency->currency_code;
        }

        $notify[] = 'Transactions data';
        return response()->json([
            'remark'  => 'transactions',
            'status'  => 'success',
            'message' => ['success' => $notify],
            'data'    => [
                'operations'   => $operations,
                'times'        => $times,
                'currencies'   => $currencies,
                'transactions' => $transactions,
            ],
        ]);
    }

    public function submitProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstname' => 'required',
            'lastname'  => 'required',
            'image'     => ['image', new FileTypeValidate(['jpg', 'jpeg', 'png'])],
        ], [
            'firstname.required' => 'The first name field is required',
            'lastname.required'  => 'The last name field is required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => $validator->errors()->all()],
            ]);
        }

        $user = auth()->user();

        if ($request->hasFile('image')) {
            try {
                $old         = $user->image;
                $user->image = fileUploader($request->image, getFilePath('userProfile'), getFileSize('userProfile'), $old);
            } catch (\Exception $exp) {
                return response()->json([
                    'remark'  => 'validation_error',
                    'status'  => 'error',
                    'message' => ['error' => ['Couldn\'t upload your image']],
                ]);
            }
        }

        $user->firstname = $request->firstname;
        $user->lastname  = $request->lastname;

        $user->address = $request->address;
        $user->city    = $request->city;
        $user->state   = $request->state;
        $user->zip     = $request->zip;

        $user->save();

        $notify[] = 'Profile updated successfully';
        return response()->json([
            'remark'  => 'profile_updated',
            'status'  => 'success',
            'message' => ['success' => $notify],
        ]);
    }

    public function submitPassword(Request $request)
    {
        $passwordValidation = Password::min(6);
        if (gs('secure_password')) {
            $passwordValidation = $passwordValidation->mixedCase()->numbers()->symbols()->uncompromised();
        }

        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'password'         => ['required', 'confirmed', $passwordValidation],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => $validator->errors()->all()],
            ]);
        }

        $user = auth()->user();
        if (Hash::check($request->current_password, $user->password)) {
            $password       = Hash::make($request->password);
            $user->password = $password;
            $user->save();
            $notify[] = 'Password changed successfully';
            return response()->json([
                'remark'  => 'password_changed',
                'status'  => 'success',
                'message' => ['success' => $notify],
            ]);
        } else {
            $notify[] = 'The password doesn\'t match!';
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => $notify],
            ]);
        }
    }

    public function addDeviceToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => $validator->errors()->all()],
            ]);
        }

        $deviceToken = DeviceToken::where('token', $request->token)->where('user_type', 'USER')->first();

        if ($deviceToken) {
            $notify[] = 'Token already exists';
            return response()->json([
                'remark'  => 'token_exists',
                'status'  => 'error',
                'message' => ['error' => $notify],
            ]);
        }

        $deviceToken            = new DeviceToken();
        $deviceToken->user_id   = auth()->user()->id;
        $deviceToken->user_type = 'USER';
        $deviceToken->token     = $request->token;
        $deviceToken->is_app    = Status::YES;
        $deviceToken->save();

        $notify[] = 'Token saved successfully';
        return response()->json([
            'remark'  => 'token_saved',
            'status'  => 'success',
            'message' => ['success' => $notify],
        ]);
    }

    public function show2faForm()
    {
        $ga        = new GoogleAuthenticator();
        $user      = auth()->user();
        $secret    = $ga->createSecret();
        $qrCodeUrl = $ga->getQRCodeGoogleUrl($user->username . '@' . gs('site_name'), $secret);
        $notify[]  = '2FA Qr';
        return response()->json([
            'remark'  => '2fa_qr',
            'status'  => 'success',
            'message' => ['success' => $notify],
            'data'    => [
                'secret'      => $secret,
                'qr_code_url' => $qrCodeUrl,
            ],
        ]);
    }

    public function create2fa(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'secret' => 'required',
            'code'   => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => $validator->errors()->all()],
            ]);
        }

        $user     = auth()->user();
        $response = verifyG2fa($user, $request->code, $request->secret);
        if ($response) {
            $user->tsc = $request->secret;
            $user->ts  = Status::ENABLE;
            $user->save();

            $notify[] = 'Google authenticator activated successfully';
            return response()->json([
                'remark'  => '2fa_qr',
                'status'  => 'success',
                'message' => ['success' => $notify],
            ]);
        } else {
            $notify[] = 'Wrong verification code';
            return response()->json([
                'remark'  => 'wrong_verification',
                'status'  => 'error',
                'message' => ['error' => $notify],
            ]);
        }
    }

    public function disable2fa(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => $validator->errors()->all()],
            ]);
        }

        $user     = auth()->user();
        $response = verifyG2fa($user, $request->code);
        if ($response) {
            $user->tsc = null;
            $user->ts  = Status::DISABLE;
            $user->save();
            $notify[] = 'Two factor authenticator deactivated successfully';
            return response()->json([
                'remark'  => '2fa_qr',
                'status'  => 'success',
                'message' => ['success' => $notify],
            ]);
        } else {
            $notify[] = 'Wrong verification code';
            return response()->json([
                'remark'  => 'wrong_verification',
                'status'  => 'error',
                'message' => ['error' => $notify],
            ]);
        }
    }

    public function pushNotifications()
    {
        $notifications = NotificationLog::where('user_id', auth()->id())->where('sender', 'firebase')->orderBy('id', 'desc')->apiQuery();
        $notify[]      = 'Push notifications';
        return response()->json([
            'remark'  => 'notifications',
            'status'  => 'success',
            'message' => ['success' => $notify],
            'data'    => [
                'notifications' => $notifications,
            ],
        ]);
    }

    public function pushNotificationsRead($id)
    {
        $notification = NotificationLog::where('user_id', auth()->id())->where('sender', 'firebase')->find($id);
        if (!$notification) {
            $notify[] = 'Notification not found';
            return response()->json([
                'remark'  => 'notification_not_found',
                'status'  => 'error',
                'message' => ['error' => $notify],
            ]);
        }
        $notify[]                = 'Notification marked as read successfully';
        $notification->user_read = Status::YES;
        $notification->save();

        return response()->json([
            'remark'  => 'notification_read',
            'status'  => 'success',
            'message' => ['success' => $notify],
        ]);
    }

    public function userInfo()
    {
        $notify[] = 'User information';
        return response()->json([
            'remark'  => 'user_info',
            'status'  => 'success',
            'message' => ['success' => $notify],
            'data'    => [
                'user' => auth()->user(),
            ],
        ]);
    }

    public function deleteAccount()
    {
        $user              = auth()->user();
        $user->username    = 'deleted_' . $user->username;
        $user->email       = 'deleted_' . $user->email;
        $user->provider_id = 'deleted_' . $user->provider_id;
        $user->save();

        $user->tokens()->delete();

        $notify[] = 'Account deleted successfully';
        return response()->json([
            'remark'  => 'account_deleted',
            'status'  => 'success',
            'message' => ['success' => $notify],
        ]);
    }

    public function qrCode()
    {
        $notify[]   = 'QR Code';
        $user       = auth()->user();
        $qrCode     = $user->createQrCode();
        $uniqueCode = $qrCode->unique_code;
        $qrCode     = cryptoQR($uniqueCode);

        return response()->json([
            'remark'  => 'qr_code',
            'status'  => 'success',
            'message' => ['success' => $notify],
            'data'    => [
                'qr_code' => $qrCode,
            ],
        ]);
    }

    public function qrCodeDownload()
    {
        $user    = auth()->user();
        $qrCode  = $user->qrCode()->first();
        $general = gs();

        $file     = cryptoQR($qrCode->unique_code);
        $filename = $qrCode->unique_code . '.jpg';

        $manager  = new ImageManager(new Driver());
        $template = $manager->read('assets/images/qr_code_template/' . $general->qr_code_template);

        $client       = new Client();
        $response     = $client->get($file);
        $imageContent = $response->getBody()->getContents();

        $qrCode = $manager->read($imageContent)->cover(2000, 2000);
        $template->place($qrCode, 'center');
        $image = $template->encode();

        $headers = [
            'Content-Type'        => 'image/jpeg',
            'Content-Disposition' => 'attachment; filename=' . $filename,
        ];

        return response()->stream(function () use ($image) {
            echo $image;
        }, 200, $headers);
    }

    public function qrCodeRemove(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file_name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => $validator->errors()->all()],
            ]);
        }

        $file = getFilePath('temporary') . '/' . $request->file_name;

        if (file_exists($file)) {
            unlink($file);

            return response()->json([
                'remark'  => 'qr_code_remove',
                'status'  => 'success',
                'message' => ['success' => 'QR code removed successfully'],
            ]);
        }

        return response()->json([
            'remark'  => 'qr_code_remove',
            'status'  => 'success',
            'message' => ['success' => 'Already removed'],
        ]);
    }

    public function qrCodeScan(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'code' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => $validator->errors()->all()],
            ]);
        }

        $data = QrCode::where('unique_code', $request->code)->first();
        if (!$data) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => ['QR code doesn\'t match']],
            ]);
        }

        if ($data->user_type == 'USER' && $data->user_id == auth()->user('user')->id) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => ["Can't transact because it's your QR code."]],
            ]);
        }

        return response()->json([
            'remark'  => 'qr_code_scan',
            'status'  => 'success',
            'message' => ['success' => ['QR code scan']],
            'data'    => [
                'user_type' => $data->user_type,
                'user_data' => $data->getUser,
            ],
        ]);
    }

    public function logoutOtherDevices(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => $validator->errors()->all()],
            ]);
        }

        $password = $request->password;

        if (!Hash::check($password, auth()->user()->password)) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => ['The password doesn\'t match!']],
            ]);
        }

        Auth::guard('web')->setUser(auth()->user())->logoutOtherDevices($password);

        return response()->json([
            'remark'  => 'logout_other_devices',
            'status'  => 'success',
            'message' => ['success' => ['Logout from other devices']],
        ]);
    }

    public function wallets()
    {
        $notify[] = "All Wallets";
        $wallets  = Wallet::hasCurrency()->where('user_id', auth()->user()->id)->where('user_type', 'USER')
            ->with('currency')->orderBy('balance', 'DESC')
            ->get();

        return response()->json([
            'remark'  => 'all_wallets',
            'status'  => 'success',
            'message' => ['success' => $notify],
            'data'    => [
                'wallets' => $wallets,
            ],
        ]);
    }
}
