<?php

namespace App\Http\Controllers\Merchant;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Lib\FormProcessor;
use App\Lib\GoogleAuthenticator;
use App\Models\DeviceToken;
use App\Models\Form;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Rules\FileTypeValidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class MerchantController extends Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function home()
    {
        $merchant        = merchant();
        $pageTitle       = "Merchant Dashboard";
        $wallets         = merchant()->topTransactedWallets()->take(3)->with('currency')->get();
        $totalAddMoney   = merchant()->totalDeposit();
        $totalWithdraw   = merchant()->totalWithdraw();
        $report          = merchant()->trxGraph();
        $histories       = Transaction::where('user_id', $merchant->id)->where('user_type', 'MERCHANT')->with('currency', 'receiverUser')->orderBy('id', 'desc')->take(10)->get();
        $totalMoneyInOut = merchant()->moneyInOut();

        $userKyc  = Form::where('act', 'merchant_kyc')->first();
        $kyc      = merchant()->kycStyle();
        $merchant = merchant();

        return view('Template::merchant.dashboard', compact('pageTitle', 'totalAddMoney', 'totalWithdraw', 'wallets', 'histories', 'totalMoneyInOut', 'kyc', 'userKyc', 'report', 'merchant'));
    }

    public function wallets()
    {
        $pageTitle = "All Wallets";
        $wallets   = Wallet::hasCurrency()->where('user_id', merchant()->id)->where('user_type', 'MERCHANT')->with('currency')->orderBy('balance', 'DESC')->get();
        return view('Template::merchant.all_wallets', compact('pageTitle', 'wallets'));
    }

    public function checkInsight(Request $req)
    {
        if ($req->day) {
            $totalMoneyInOut = merchant()->moneyInOut($req->day);
            return response()->json($totalMoneyInOut);
        }
        return response()->json(['error' => 'Sorry can\'t process your request right now']);
    }

    public function apiKey()
    {

        $pageTitle = "Business Api Key";
        $merchant  = merchant();

        if (!$merchant->public_api_key || !$merchant->secret_api_key) {
            $merchant->public_api_key = keyGenerator();
            $merchant->secret_api_key = keyGenerator();
            $merchant->save();
        }

        return view('Template::merchant.api_key', compact('pageTitle'));
    }

    public function generateApiKey()
    {

        $merchant  = merchant();
        $publicKey = keyGenerator();
        $secretKey = keyGenerator();

        $merchant->public_api_key = $publicKey;
        $merchant->secret_api_key = $secretKey;
        $merchant->save();

        $notify[] = ['success', 'New API key generated successfully'];
        return back()->withNotify($notify);
    }

    public function profile()
    {
        $pageTitle = "Profile Setting";
        $user      = merchant();
        return view('Template::merchant.profile_setting', compact('pageTitle', 'user'));
    }

    public function submitProfile(Request $request)
    {

        $request->validate([
            'firstname' => 'required|string',
            'lastname'  => 'required|string',
            'image'     => ['image', new FileTypeValidate(['jpg', 'jpeg', 'png'])],
        ], [
            'firstname.required' => 'First name field is required',
            'lastname.required'  => 'Last name field is required',
        ]);

        $user = merchant();

        $user->firstname = $request->firstname;
        $user->lastname  = $request->lastname;

        $user->address = [
            'address' => $request->address,
            'state'   => $request->state,
            'zip'     => $request->zip,
            'country' => @$user->address->country,
            'city'    => $request->city,
        ];

        if ($request->hasFile('image')) {
            try {
                $old         = $user->image;
                $user->image = fileUploader($request->image, getFilePath('merchantProfile'), getFileSize('merchantProfile'), $old);
            } catch (\Exception $exp) {
                $notify[] = ['error', 'Couldn\'t upload your image'];
                return back()->withNotify($notify);
            }
        }

        $user->save();

        $notify[] = ['success', 'Profile updated successfully.'];
        return back()->withNotify($notify);
    }

    public function changePassword()
    {
        $pageTitle = 'Change password';
        return view('Template::merchant.password', compact('pageTitle'));
    }

    public function submitPassword(Request $request)
    {

        $passwordValidation = Password::min(6);
        $general            = gs();

        if ($general->secure_password) {
            $passwordValidation = $passwordValidation->mixedCase()->numbers()->symbols()->uncompromised();
        }

        $request->validate([
            'current_password' => 'required',
            'password'         => ['required', 'confirmed', $passwordValidation],
        ]);

        $user = merchant();

        if (Hash::check($request->current_password, $user->password)) {
            $password       = Hash::make($request->password);
            $user->password = $password;
            $user->save();

            $notify[] = ['success', 'Password changes successfully'];
            return back()->withNotify($notify);
        } else {
            $notify[] = ['error', 'The password doesn\'t match!'];
            return back()->withNotify($notify);
        }
    }

    public function show2faForm()
    {
        $general   = gs();
        $ga        = new GoogleAuthenticator();
        $merchant  = merchant();
        $secret    = $ga->createSecret();
        $qrCodeUrl = $ga->getQRCodeGoogleUrl($merchant->username . '@' . $general->sitename, $secret);
        $pageTitle = 'Two Factor';
        return view('Template::merchant.twofactor', compact('pageTitle', 'secret', 'qrCodeUrl', 'merchant'));
    }

    public function create2fa(Request $request)
    {
        $request->validate([
            'key'  => 'required',
            'code' => 'required',
        ]);

        $user     = merchant();
        $response = verifyG2fa($user, $request->code, $request->key);

        if ($response) {
            $user->tsc = $request->key;
            $user->ts  = 1;
            $user->save();

            $notify[] = ['success', 'Google authenticator enabled successfully'];
            return back()->withNotify($notify);
        } else {
            $notify[] = ['error', 'Wrong verification code'];
            return back()->withNotify($notify);
        }
    }

    public function disable2fa(Request $request)
    {
        $request->validate([
            'code' => 'required',
        ]);

        $user     = merchant();
        $response = verifyG2fa($user, $request->code);

        if ($response) {
            $user->tsc = null;
            $user->ts  = 0;
            $user->save();

            $notify[] = ['success', 'Two factor authenticator disable successfully'];
        } else {
            $notify[] = ['error', 'Wrong verification code'];
        }
        return back()->withNotify($notify);
    }

    public function trxHistory(Request $request)
    {
        $pageTitle = $request->search ? "Search Result of #$request->search" : "Transaction History";
        $histories = merchant()->trxLog($request);
        return view('Template::merchant.trx_history', compact('pageTitle', 'histories'));
    }

    public function kycForm()
    {
        $merchant = merchant();

        if ($merchant->kv == Status::KYC_PENDING) {
            $notify[] = ['error', 'Your KYC is under review'];
            return to_route('merchant.home')->withNotify($notify);
        }
        if ($merchant->kv == Status::KYC_VERIFIED) {
            $notify[] = ['error', 'You are already KYC verified'];
            return to_route('merchant.home')->withNotify($notify);
        }

        $pageTitle = 'KYC Form';
        $form      = Form::where('act', 'merchant_kyc')->first();

        return view('Template::merchant.kyc.form', compact('pageTitle', 'form'));
    }

    public function kycSubmit(Request $request)
    {

        $form     = Form::where('act', 'merchant_kyc')->first();
        $formData = $form->form_data;

        $formProcessor  = new FormProcessor();
        $validationRule = $formProcessor->valueValidation($formData);

        $request->validate($validationRule);
        $userData = $formProcessor->processFormData($request, $formData);

        $user           = merchant();
        $user->kyc_data = $userData;
        $user->kv       = 2;
        $user->save();

        $notify[] = ['success', 'KYC data submitted successfully'];
        return to_route('merchant.home')->withNotify($notify);
    }

    public function kycData()
    {
        $user      = merchant();
        $pageTitle = 'KYC Data';
        abort_if($user->kv == Status::VERIFIED, 403);
        return view('Template::merchant.kyc.info', compact('pageTitle', 'user'));
    }

    public function qrCode()
    {
        $pageTitle  = 'QR Code';
        $user       = userGuard()['user'];
        $qrCode     = $user->createQrCode();
        $uniqueCode = $qrCode->unique_code;
        $qrCode     = cryptoQR($uniqueCode);
        return view('Template::merchant.qr_code', compact('pageTitle', 'qrCode', 'uniqueCode'));
    }

    public function downLoadQrCodeJpg()
    {
        $user   = userGuard()['user'];
        $qrCode = $user->downLoadQrCode();
        return $qrCode;
    }

    public function userData()
    {
        $user = merchant();
        if ($user->profile_complete == Status::YES) {
            return to_route('merchant.home');
        }

        $pageTitle = 'Merchant Data';
        return view('Template::merchant.user_data', compact('pageTitle', 'user'));
    }

    public function userDataSubmit(Request $request)
    {

        $user = merchant();

        if ($user->profile_complete == Status::YES) {
            return to_route('merchant.home');
        }

        $request->validate([
            'firstname' => 'required',
            'lastname'  => 'required',
        ]);

        $user->firstname = $request->firstname;
        $user->lastname  = $request->lastname;
        $user->address   = [
            'country' => @$user->address->country,
            'address' => $request->address,
            'state'   => $request->state,
            'zip'     => $request->zip,
            'city'    => $request->city,
        ];

        $user->profile_complete = Status::YES;
        $user->save();

        $notify[] = ['success', 'Registration process completed successfully'];
        return to_route('merchant.home')->withNotify($notify);
    }

    public function addDeviceToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
        ]);

        if ($validator->fails()) {
            return ['success' => false, 'errors' => $validator->errors()->all()];
        }

        $deviceToken = DeviceToken::where('token', $request->token)->first();

        if ($deviceToken) {
            return ['success' => true, 'message' => 'Already exists'];
        }

        $deviceToken            = new DeviceToken();
        $deviceToken->user_id   = auth('merchant')->id();
        $deviceToken->user_type = 'MERCHANT';
        $deviceToken->token     = $request->token;
        $deviceToken->is_app    = Status::NO;
        $deviceToken->save();

        return ['success' => true, 'message' => 'Token saved successfully'];
    }

    public function attachmentDownload($fileHash)
    {
        $filePath  = decrypt($fileHash);
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $general   = gs();
        $title     = slug($general->site_name) . '- attachments.' . $extension;
        $mimetype  = mime_content_type($filePath);
        header('Content-Disposition: attachment; filename="' . $title);
        header("Content-Type: " . $mimetype);
        return readfile($filePath);
    }

}
