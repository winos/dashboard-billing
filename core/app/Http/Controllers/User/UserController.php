<?php

namespace App\Http\Controllers\User;

use App\Models\Form;
use App\Models\Wallet;
use App\Models\Deposit;
use App\Constants\Status;
use App\Lib\FormProcessor;
use App\Models\DeviceToken;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Lib\GoogleAuthenticator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function home()
    {
        $pageTitle = 'Dashboard';
        $user      = auth()->user();

        $wallets = $user->topTransactedWallets()->with('currency');

        $totalBalance[] = 0;
        foreach ($wallets->get() as $wallet) {
            $totalBalance[] = $wallet->balance * $wallet->currency->rate;
        }

        $wallets         = $wallets->take(3)->get();
        $totalMoneyInOut = $user->moneyInOut();

        $kyc       = $user->kycStyle();
        $histories = Transaction::where('user_id', $user->id)->where('user_type', 'USER')
            ->with('currency', 'receiverUser', 'receiverAgent', 'receiverMerchant')->orderBy('id', 'desc')->take(10)
            ->get();

        return view('Template::user.dashboard', compact('pageTitle', 'wallets', 'totalBalance', 'totalMoneyInOut', 'kyc', 'user', 'histories'));
    }

    public function depositHistory(Request $request)
    {
        $pageTitle = 'Deposit History';
        $deposits  = auth()->user()->deposits()->searchable(['trx'])->with(['gateway'])->orderBy('id', 'desc')->paginate(getPaginate());
        return view('Template::user.deposit_history', compact('pageTitle', 'deposits'));
    }

    public function show2faForm()
    {
        $ga        = new GoogleAuthenticator();
        $user      = auth()->user();
        $secret    = $ga->createSecret();
        $qrCodeUrl = $ga->getQRCodeGoogleUrl($user->username . '@' . gs('site_name'), $secret);
        $pageTitle = '2FA Security';
        return view('Template::user.twofactor', compact('pageTitle', 'secret', 'qrCodeUrl', 'user'));
    }

    public function create2fa(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'key'  => 'required',
            'code' => 'required',
        ]);
        $response = verifyG2fa($user, $request->code, $request->key);
        if ($response) {
            $user->tsc = $request->key;
            $user->ts  = Status::ENABLE;
            $user->save();
            $notify[] = ['success', 'Two factor authenticator activated successfully'];
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

        $user     = auth()->user();
        $response = verifyG2fa($user, $request->code);
        if ($response) {
            $user->tsc = null;
            $user->ts  = Status::DISABLE;
            $user->save();
            $notify[] = ['success', 'Two factor authenticator deactivated successfully'];
        } else {
            $notify[] = ['error', 'Wrong verification code'];
        }
        return back()->withNotify($notify);
    }

    public function transactions(Request $request)
    {
        $pageTitle = $request->search ? "Search Result of #$request->search" : "Transaction History";
        $transactions = auth()->user()->trxLog($request);
        return view('Template::user.transactions', compact('pageTitle', 'transactions'));
    }

    public function kycForm()
    {
        if (auth()->user()->kv == Status::KYC_PENDING) {
            $notify[] = ['error', 'Your KYC is under review'];
            return to_route('user.home')->withNotify($notify);
        }
        if (auth()->user()->kv == Status::KYC_VERIFIED) {
            $notify[] = ['error', 'You are already KYC verified'];
            return to_route('user.home')->withNotify($notify);
        }
        $pageTitle = 'KYC Form';
        $form      = Form::where('act', 'user_kyc')->first();
        return view('Template::user.kyc.form', compact('pageTitle', 'form'));
    }

    public function kycData()
    {
        $user      = auth()->user();
        $pageTitle = 'KYC Data';
        abort_if($user->kv == Status::VERIFIED, 403);
        return view('Template::user.kyc.info', compact('pageTitle', 'user'));
    }

    public function kycSubmit(Request $request)
    {
        $form           = Form::where('act', 'user_kyc')->firstOrFail();
        $formData       = $form->form_data;
        $formProcessor  = new FormProcessor();
        $validationRule = $formProcessor->valueValidation($formData);
        $request->validate($validationRule);
        $user = auth()->user();
        foreach (@$user->kyc_data ?? [] as $kycData) {
            if ($kycData->type == 'file') {
                fileManager()->removeFile(getFilePath('verify') . '/' . $kycData->value);
            }
        }
        $userData                   = $formProcessor->processFormData($request, $formData);
        $user->kyc_data             = $userData;
        $user->kyc_rejection_reason = null;
        $user->kv                   = Status::KYC_PENDING;
        $user->save();

        $notify[] = ['success', 'KYC data submitted successfully'];
        return to_route('user.home')->withNotify($notify);

    }

    public function userData()
    {
        $user = auth()->user();

        if ($user->profile_complete == Status::YES) {
            return to_route('user.home');
        }

        $pageTitle  = 'User Data';
        $info       = json_decode(json_encode(getIpInfo()), true);
        $mobileCode = @implode(',', $info['code']);
        $countries  = json_decode(file_get_contents(resource_path('views/partials/country.json')));

        return view('Template::user.user_data', compact('pageTitle', 'user', 'countries', 'mobileCode'));
    }

    public function userDataSubmit(Request $request)
    {

        $user = auth()->user();

        if ($user->profile_complete == Status::YES) {
            return to_route('user.home');
        }

        $countryData  = (array) json_decode(file_get_contents(resource_path('views/partials/country.json')));
        $countryCodes = implode(',', array_keys($countryData));
        $mobileCodes  = implode(',', array_column($countryData, 'dial_code'));
        $countries    = implode(',', array_column($countryData, 'country'));

        $request->validate([
            'country_code' => 'required|in:' . $countryCodes,
            'country'      => 'required|in:' . $countries,
            'mobile_code'  => 'required|in:' . $mobileCodes,
            'username'     => 'required|unique:users|min:6',
            'mobile'       => ['required', 'regex:/^([0-9]*)$/', Rule::unique('users')->where('dial_code', $request->mobile_code)],
        ]);

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

        return to_route('user.home');
    }

    public function qrCode()
    {
        $pageTitle  = 'QR Code';
        $user       = userGuard()['user'];
        $qrCode     = $user->createQrCode();
        $uniqueCode = $qrCode->unique_code;
        $qrCode     = cryptoQR($uniqueCode);
        return view('Template::user.qr_code', compact('pageTitle', 'qrCode', 'uniqueCode'));
    }

    public function downLoadQrCodeJpg()
    {
        $user   = userGuard()['user'];
        $qrCode = $user->downLoadQrCode();
        return $qrCode;
    }

    public function checkInsight(Request $request)
    {
        if ($request->day) {
            $totalMoneyInOut = auth()->user()->moneyInOut($request->day);
            return response()->json($totalMoneyInOut);
        }
        return response()->json(['error' => 'Sorry can\'t process your request right now']);
    }

    public function wallets()
    {
        $pageTitle = "All Wallets";
        $wallets   = Wallet::hasCurrency()->where('user_id', auth()->user()->id)->where('user_type', 'USER')
            ->with('currency')->orderBy('balance', 'DESC')
            ->get();
        return view('Template::user.wallets', compact('pageTitle', 'wallets'));
    }

    public function logoutOtherDevicesForm()
    {
        $pageTitle = 'Logout From Other Devices';
        return view('Template::user.logout_form', compact('pageTitle'));
    }

    public function logoutOtherDevices(Request $request)
    {
        $request->validate([
            'password' => 'required',
        ]);

        $password = $request->password;

        if (!Hash::check($password, auth()->user()->password)) {
            $notify[] = ['error', 'The password doesn\'t match!'];
            return back()->withNotify($notify);
        }

        Auth::logoutOtherDevices($password);

        $notify[] = ['success', 'Successfully logged out from other devices'];
        return back()->withNotify($notify);
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
        $deviceToken->user_id   = auth()->user()->id;
        $deviceToken->user_type = 'USER';
        $deviceToken->token     = $request->token;
        $deviceToken->is_app    = Status::NO;
        $deviceToken->save();

        return ['success' => true, 'message' => 'Token saved successfully'];
    }

    public function downloadAttachment($fileHash)
    {
        $filePath  = decrypt($fileHash);
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $title     = slug(gs('site_name')) . '- attachments.' . $extension;
        try {
            $mimetype = mime_content_type($filePath);
        } catch (\Exception $e) {
            $notify[] = ['error', 'File does not exists'];
            return back()->withNotify($notify);
        }
        header('Content-Disposition: attachment; filename="' . $title);
        header("Content-Type: " . $mimetype);
        return readfile($filePath);
    }

}
