<?php

namespace App\Http\Controllers\Agent;

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

class AgentController extends Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function home()
    {
        $agent         = agent();
        $pageTitle     = "Agent Dashboard";
        $wallets       = $agent->topTransactedWallets()->take(3)->with('currency')->get();
        $totalAddMoney = $agent->totalDeposit();
        $totalWithdraw = $agent->totalWithdraw();
        $report        = $agent->trxGraph();

        $userKyc   = Form::where('act', 'agent_kyc')->first();
        $histories = Transaction::where('user_id', $agent->id)->where('user_type', 'AGENT')->with('currency', 'receiverUser')
            ->orderBy('id', 'desc')->take(10)
            ->get();

        $totalMoneyInOut = $agent->moneyInOut();
        $kyc             = $agent->kycStyle();

        return view('Template::agent.dashboard', compact(
            'pageTitle', 'wallets', 'histories', 'totalMoneyInOut', 'userKyc', 'kyc', 'totalAddMoney', 'totalWithdraw', 'report', 'agent')
        );
    }

    public function wallets()
    {
        $pageTitle = "All Wallets";
        $wallets   = Wallet::hasCurrency()->where('user_id', agent()->id)->where('user_type', 'AGENT')->with('currency')->orderBy('balance', 'DESC')->get();
        return view('Template::agent.all_wallets', compact('pageTitle', 'wallets'));
    }

    public function checkInsight(Request $req)
    {
        if ($req->day) {
            $totalMoneyInOut = agent()->moneyInOut($req->day);
            return response()->json($totalMoneyInOut);
        }
        return response()->json(['error' => 'Sorry can\'t process your request right now']);
    }

    public function profile()
    {
        $pageTitle = "Profile Setting";
        $user      = agent();
        return view('Template::agent.profile_setting', compact('pageTitle', 'user'));
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

        $user = agent();

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
                $user->image = fileUploader($request->image, getFilePath('agentProfile'), getFileSize('agentProfile'), $old);
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
        return view('Template::agent.password', compact('pageTitle'));
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

        $user = agent();

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
        $agent     = agent();
        $secret    = $ga->createSecret();
        $qrCodeUrl = $ga->getQRCodeGoogleUrl($agent->username . '@' . $general->sitename, $secret);
        $pageTitle = 'Two Factor';
        return view('Template::agent.twofactor', compact('pageTitle', 'secret', 'qrCodeUrl', 'agent'));
    }

    public function create2fa(Request $request)
    {

        $user = agent();
        $request->validate([
            'key'  => 'required',
            'code' => 'required',
        ]);

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

        $user     = agent();
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
        $histories = agent()->trxLog($request);
        return view('Template::agent.trx_history', compact('pageTitle', 'histories'));
    }

    public function depositHistory()
    {
        $pageTitle = 'Add Money History';
        $logs      = agent()->deposits()->with('gateway')->orderBy('id', 'desc')->with('currency')->paginate(getPaginate());
        return view('Template::agent.deposit_history', compact('pageTitle', 'logs'));
    }

    public function kycForm()
    {
        $agent = agent();
        if ($agent->kv == Status::KYC_PENDING) {
            $notify[] = ['error', 'Your KYC is under review'];
            return to_route('agent.home')->withNotify($notify);
        }
        if ($agent->kv == Status::KYC_VERIFIED) {
            $notify[] = ['error', 'You are already KYC verified'];
            return to_route('agent.home')->withNotify($notify);
        }

        $pageTitle = 'KYC Form';
        $form      = Form::where('act', 'agent_kyc')->first();

        return view('Template::agent.kyc.form', compact('pageTitle', 'form'));
    }

    public function kycSubmit(Request $request)
    {
        $form     = Form::where('act', 'agent_kyc')->first();
        $formData = $form->form_data;

        $formProcessor  = new FormProcessor();
        $validationRule = $formProcessor->valueValidation($formData);

        $request->validate($validationRule);
        $userData = $formProcessor->processFormData($request, $formData);

        $user           = agent();
        $user->kyc_data = $userData;
        $user->kv       = 2;
        $user->save();

        $notify[] = ['success', 'KYC data submitted successfully'];
        return to_route('agent.home')->withNotify($notify);
    }

    public function kycData()
    {
        $user      = agent();
        $pageTitle = 'KYC Data';
        abort_if($user->kv == Status::VERIFIED, 403);
        return view('Template::agent.kyc.info', compact('pageTitle', 'user'));
    }

    public function qrCode()
    {
        $pageTitle  = 'QR Code';
        $user       = userGuard()['user'];
        $qrCode     = $user->createQrCode();
        $uniqueCode = $qrCode->unique_code;
        $qrCode     = cryptoQR($uniqueCode);
        return view('Template::agent.qr_code', compact('pageTitle', 'qrCode', 'uniqueCode'));
    }

    public function downLoadQrCodeJpg()
    {
        $user   = userGuard()['user'];
        $qrCode = $user->downLoadQrCode();
        return $qrCode;
    }

    public function commissionLog()
    {
        $pageTitle = "Commission Logs";
        $logs      = Transaction::where('user_type', 'AGENT')->where('user_id', agent()->id)->where('remark', 'commission')
            ->with('currency')->orderBy('id', 'DESC')
            ->paginate(getPaginate());
        return view('Template::agent.commission_log', compact('pageTitle', 'logs'));
    }

    public function userData()
    {
        $user = agent();

        if ($user->profile_complete == 1) {
            return to_route('agent.home');
        }

        $pageTitle = 'Agent Data';
        return view('Template::agent.user_data', compact('pageTitle', 'user'));
    }

    public function userDataSubmit(Request $request)
    {

        $user = agent();

        if ($user->profile_complete == 1) {
            return to_route('agent.home');
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

        $user->profile_complete = 1;
        $user->save();

        $notify[] = ['success', 'Registration process completed successfully'];
        return to_route('agent.home')->withNotify($notify);
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
        $deviceToken->user_id   = auth('agent')->id();
        $deviceToken->user_type = 'AGENT';
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
