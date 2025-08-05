<?php

namespace App\Http\Controllers\Admin;

use App\Models\Wallet;
use App\Models\Deposit;
use App\Constants\Status;
use App\Models\Withdrawal;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\NotificationLog;
use App\Rules\FileTypeValidate;
use App\Lib\UserNotificationSender;
use App\Http\Controllers\Controller;
use App\Models\NotificationTemplate;
use Illuminate\Support\Facades\Auth;

class ManageGuardController extends Controller
{

    private $type;
    private $model;
    private $guardType;

    public function __construct(Request $request)
    {

        $request = @$request->route()->action;

        $this->guardType = @$request['guardType'];
        $this->model     = ucfirst(substr($this->guardType, 0, -1));
        $this->type      = strtoupper($this->model);
        $this->model     = "App\\Models\\$this->model";

    }

    public function allUsers()
    {
        $pageTitle = 'All ' . ucfirst($this->guardType);
        $users     = $this->getData();
        return view("admin.$this->guardType.list", compact('pageTitle', 'users'));
    }

    public function activeUsers()
    {
        $pageTitle = 'Active ' . ucfirst($this->guardType);
        $users     = $this->getData('active');
        return view("admin.$this->guardType.list", compact('pageTitle', 'users'));
    }

    public function bannedUsers()
    {
        $pageTitle = 'Banned ' . ucfirst($this->guardType);
        $users     = $this->getData('banned');
        return view("admin.$this->guardType.list", compact('pageTitle', 'users'));
    }

    public function emailUnverifiedUsers()
    {
        $pageTitle = 'Email Unverified ' . ucfirst($this->guardType);
        $users     = $this->getData('emailUnverified');
        return view("admin.$this->guardType.list", compact('pageTitle', 'users'));
    }

    public function kycUnverifiedUsers()
    {
        $pageTitle = 'KYC Unverified ' . ucfirst($this->guardType);
        $users     = $this->getData('kycUnverified');
        return view("admin.$this->guardType.list", compact('pageTitle', 'users'));
    }

    public function kycPendingUsers()
    {
        $pageTitle = 'KYC Pending ' . ucfirst($this->guardType);
        $users     = $this->getData('kycPending');
        return view("admin.$this->guardType.list", compact('pageTitle', 'users'));
    }

    public function emailVerifiedUsers()
    {
        $pageTitle = 'Email Verified ' . ucfirst($this->guardType);
        $users     = $this->getData('emailVerified');
        return view("admin.$this->guardType.list", compact('pageTitle', 'users'));
    }

    public function mobileUnverifiedUsers()
    {
        $pageTitle = 'Mobile Unverified ' . ucfirst($this->guardType);
        $users     = $this->getData('mobileUnverified');
        return view("admin.$this->guardType.list", compact('pageTitle', 'users'));
    }

    public function mobileVerifiedUsers()
    {
        $pageTitle = 'Mobile Verified ' . ucfirst($this->guardType);
        $users     = $this->getData('mobileVerified');
        return view("admin.$this->guardType.list", compact('pageTitle', 'users'));
    }

    public function usersWithBalance()
    {
        $pageTitle = ucfirst($this->guardType) . ' with Balance';
        $users     = $this->getData('withBalance');
        return view("admin.$this->guardType.list", compact('pageTitle', 'users'));
    }

    protected function getData($scope = null)
    {

        if ($scope) {
            $users = $this->model::$scope();
        } else {
            $users = $this->model::query();
        }

        return $users->searchable(['username', 'email'])->orderBy('id', 'desc')->paginate(getPaginate());
    }

    public function detail($id)
    {
        $user      = $this->model::findOrFail($id);
        $pageTitle = ucfirst($this->guardType) . ' Detail - ' . $user->username;
        $type      = $this->type;

        $totalDeposit = Deposit::where('user_id', $user->id)->where('deposits.status', 1)->where('user_type', $type)
            ->leftJoin('currencies', 'currencies.id', '=', 'deposits.currency_id')
            ->selectRaw("SUM(amount * currencies.rate) as amount")
            ->first('amount')->amount ?? 0;

        $totalWithdrawals = Withdrawal::where('user_id', $user->id)->where('withdrawals.status', 1)->where('user_type', $type)
            ->leftJoin('currencies', 'currencies.id', '=', 'withdrawals.currency_id')
            ->selectRaw("SUM(amount * currencies.rate) as amount")
            ->first('amount')->amount ?? 0;

        $totalTransaction = Transaction::where('user_id', $user->id)->where('user_type', $type)->count();
        $countries        = json_decode(file_get_contents(resource_path('views/partials/country.json')));
        $wallets          = Wallet::where('user_id', $user->id)->where('user_type', $type)->with('currency')->get();

        $totalMoneyIn  = 0;
        $totalMoneyOut = 0;
        $totalGetPaid  = 0;

        if ($type == 'USER') {
            $totalMoneyOut = Transaction::where('user_id', $user->id)->where('user_type', 'USER')->where('remark', 'money_out')
                ->leftJoin('currencies', 'currencies.id', '=', 'transactions.currency_id')
                ->selectRaw("SUM(amount * currencies.rate) as amount")
                ->first('amount')->amount ?? 0;
        } elseif ($type == 'AGENT') {
            $totalMoneyIn = Transaction::where('user_id', $user->id)->where('user_type', 'AGENT')->where('remark', 'money_in')
                ->leftJoin('currencies', 'currencies.id', '=', 'transactions.currency_id')
                ->selectRaw("SUM(amount * currencies.rate) as amount")
                ->first('amount')->amount ?? 0;
        } elseif ($type == 'MERCHANT') {
            $totalGetPaid = Transaction::where('user_id', $user->id)->where('user_type', 'MERCHANT')->where('remark', 'merchant_payment')
                ->leftJoin('currencies', 'currencies.id', '=', 'transactions.currency_id')
                ->selectRaw("SUM(amount * currencies.rate) as amount")
                ->first('amount')->amount ?? 0;
        }

        return view("admin.$this->guardType.detail", compact('pageTitle', 'user', 'totalDeposit', 'totalWithdrawals', 'totalTransaction', 'countries', 'wallets', 'totalMoneyOut', 'totalMoneyIn', 'totalGetPaid'));
    }

    public function kycDetails($id)
    {
        $pageTitle = 'KYC Details';
        $user      = $this->model::findOrFail($id);
        return view("admin.$this->guardType.kyc_detail", compact('pageTitle', 'user'));
    }

    public function kycApprove($id)
    {
        $user     = $this->model::findOrFail($id);
        $user->kv = Status::KYC_VERIFIED;
        $user->save();

        notify($user, 'KYC_APPROVE', []);

        $notify[] = ['success', 'KYC approved successfully'];
        return to_route("admin.$this->guardType.kyc.pending")->withNotify($notify);
    }

    public function kycReject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required',
        ]);
        $user                       = $this->model::findOrFail($id);
        $user->kv                   = Status::KYC_UNVERIFIED;
        $user->kyc_rejection_reason = $request->reason;
        $user->save();

        notify($user, 'KYC_REJECT', [
            'reason' => $request->reason,
        ]);

        $notify[] = ['success', 'KYC rejected successfully'];
        return to_route("admin.$this->guardType.kyc.pending")->withNotify($notify);   
    }

    public function update(Request $request, $id)
    {
        $user         = $this->model::findOrFail($id);
        $countryData  = json_decode(file_get_contents(resource_path('views/partials/country.json')));
        $countryArray = (array) $countryData;
        $countries    = implode(',', array_keys($countryArray));

        $countryCode = $request->country;
        $country     = $countryData->$countryCode->country;
        $dialCode    = $countryData->$countryCode->dial_code;

        $request->validate([
            'firstname' => 'required|string|max:40',
            'lastname'  => 'required|string|max:40',
            'email'     => "required|email|string|max:40|unique:$this->guardType,email," . $user->id,
            'country'   => 'required|in:' . $countries,
        ]);

        if ($this->type == 'USER') {
            $exists = $this->model::where('mobile', $request->mobile)->where('dial_code', $dialCode)->where('id', '!=', $user->id)->exists();
        } else {
            $user->mobile = $dialCode . $request->mobile;
            $exists       = $this->model::where('mobile', $user->mobile)->where('id', '!=', $user->id)->exists();
        }

        if ($exists) {
            $notify[] = ['error', 'The mobile number already exists.'];
            return back()->withNotify($notify);
        }

        $user->country_code = $countryCode;
        $user->firstname    = $request->firstname;
        $user->lastname     = $request->lastname;
        $user->email        = $request->email;

        if ($this->type == 'USER') {
            $user->mobile       = $request->mobile;
            $user->address      = $request->address;
            $user->city         = $request->city;
            $user->state        = $request->state;
            $user->zip          = $request->zip;
            $user->country_name = @$country;
            $user->dial_code    = $dialCode;
        } else {
            $user->address = [
                'address' => $request->address,
                'city'    => $request->city,
                'state'   => $request->state,
                'zip'     => $request->zip,
                'country' => @$country,
            ];
        }

        $user->ev = $request->ev ? Status::VERIFIED : Status::UNVERIFIED;
        $user->sv = $request->sv ? Status::VERIFIED : Status::UNVERIFIED;
        $user->ts = $request->ts ? Status::ENABLE : Status::DISABLE;

        if (!$request->kv) {
            $user->kv = 0;
            if ($user->kyc_data) {
                foreach ($user->kyc_data ?? [] as $kycData) {
                    if ($kycData->type == 'file') {
                        fileManager()->removeFile(getFilePath('verify') . '/' . $kycData->value);
                    }
                }
            }
            $user->kyc_data = null;
        } else {
            $user->kv = 1;
        }

        $user->save();

        $notify[] = ['success', ucfirst($this->guardType) . ' details updated successfully'];
        return back()->withNotify($notify);
    }

    public function addSubBalance(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|gt:0',
            'act'    => 'required|in:add,sub',
            'remark' => 'required|string|max:255',
        ]);

        $user   = $this->model::findOrFail($id);
        $wallet = Wallet::where('id', $request->wallet_id)->where('user_id', $user->id)->first();

        if (!$wallet) {
            $notify[] = ['error', 'Sorry wallet not found'];
            return back()->withNotify($notify);
        }

        $amount = $request->amount;
        $trx    = getTrx();

        $transaction = new Transaction();

        if ($request->act == 'add') {
            $wallet->balance += $amount;

            $transaction->trx_type = '+';
            $transaction->remark   = 'balance_add';

            $notifyTemplate = 'BAL_ADD';

            $notify[] = ['success', $wallet->currency->currency_symbol . $amount . ' added successfully'];

        } else {
            if ($amount > $wallet->balance) {
                $notify[] = ['error', $user->username . ' doesn\'t have sufficient balance.'];
                return back()->withNotify($notify);
            }

            $wallet->balance -= $amount;

            $transaction->trx_type = '-';
            $transaction->remark   = 'balance_subtract';

            $notifyTemplate = 'BAL_SUB';
            $notify[]       = ['success', $wallet->currency->currency_symbol . $amount . ' subtracted successfully'];
        }

        $wallet->save();

        $transaction->user_id       = $user->id;
        $transaction->user_type     = $this->type;
        $transaction->wallet_id     = $wallet->id;
        $transaction->currency_id   = $wallet->currency_id;
        $transaction->before_charge = $amount;
        $transaction->amount        = $amount;
        $transaction->post_balance  = $wallet->balance;
        $transaction->charge        = 0;
        $transaction->trx           = $trx;
        $transaction->details       = $request->remark;
        $transaction->save();

        notify($user, $notifyTemplate, [
            'trx'             => $trx,
            'amount'          => showAmount($amount, $wallet->currency, currencyFormat:false),
            'remark'          => $request->remark,
            'post_balance'    => showAmount($wallet->balance, $wallet->currency, currencyFormat:false),
            'wallet_currency' => $wallet->currency->currency_code,
        ]);

        return back()->withNotify($notify);
    }

    public function login($id)
    {
        $auth = strtolower($this->type);

        if ($auth != 'user') {
            Auth::guard($auth)->loginUsingId($id);
        }

        Auth::loginUsingId($id);

        logoutAnother($auth);
        return to_route("$auth.home");
    }

    public function status(Request $request, $id)
    {
        $user = $this->model::findOrFail($id);
        if ($user->status == Status::USER_ACTIVE) {
            $request->validate([
                'reason' => 'required|string|max:255',
            ]);
            $user->status     = Status::USER_BAN;
            $user->ban_reason = $request->reason;
            $notify[]         = ['success', ucfirst($this->guardType) . ' banned successfully'];
        } else {
            $user->status     = Status::USER_ACTIVE;
            $user->ban_reason = null;
            $notify[]         = ['success', ucfirst($this->guardType) . ' unbanned successfully'];
        }
        $user->save();
        return back()->withNotify($notify);
    }

    public function showNotificationSingleForm($id)
    {
        $user    = $this->model::findOrFail($id);
        $general = gs();
        if (!$general->en && !$general->sn) {
            $notify[] = ['warning', 'Notification options are disabled currently'];
            return to_route("admin.$this->guardType.detail", $user->id)->withNotify($notify);
        }
        $pageTitle = 'Send Notification to ' . $user->username;
        return view("admin.$this->guardType.notification_single", compact('pageTitle', 'user'));
    }

    public function sendNotificationSingle(Request $request, $id)
    {
        $request->validate([
            'message' => 'required',
            'via'     => 'required|in:email,sms,push',
            'subject' => 'required_if:via,email,push',
            'image'   => ['nullable', 'image', new FileTypeValidate(['jpg', 'jpeg', 'png'])],
        ]);

        if (!gs('en') && !gs('sn') && !gs('pn')) {
            $notify[] = ['warning', 'Notification options are disabled currently'];
            return to_route('admin.dashboard')->withNotify($notify);
        }

        return (new UserNotificationSender())->notificationToSingle($request, $id, ucfirst(strtolower($this->type)));
    }

    public function showNotificationAllForm()
    {
        if (!gs('en') && !gs('sn') && !gs('pn')) {
            $notify[] = ['warning', 'Notification options are disabled currently'];
            return to_route('admin.dashboard')->withNotify($notify);
        }

        $notifyToUser = $this->model::notifyToUser();
        $users        = $this->model::active()->count();
        $pageTitle    = 'Notification to Verified ' . ucfirst($this->guardType);

        if (session()->has('SEND_NOTIFICATION') && !request()->email_sent) {
            session()->forget('SEND_NOTIFICATION');
        }

        return view("admin.$this->guardType.notification_all", compact('pageTitle', 'users', 'notifyToUser'));
    }

    public function sendNotificationAll(Request $request)
    {
        $request->validate([
            'via'                          => 'required|in:email,sms,push',
            'message'                      => 'required',
            'subject'                      => 'required_if:via,email,push',
            'start'                        => 'required|integer|gte:1',
            'batch'                        => 'required|integer|gte:1',
            'being_sent_to'                => 'required',
            'cooling_time'                 => 'required|integer|gte:1',
            'number_of_top_deposited_user' => 'required_if:being_sent_to,topDepositedAgents|integer|gte:0',
            'number_of_days'               => 'required_if:being_sent_to,notLoginUsers|integer|gte:0',
            'image'                        => ["nullable", 'image', new FileTypeValidate(['jpg', 'jpeg', 'png'])],
        ], [
            'number_of_days.required_if'               => "Number of days field is required",
            'number_of_top_deposited_user.required_if' => "Number of top deposited user field is required",
        ]);

        if (!gs('en') && !gs('sn') && !gs('pn')) {
            $notify[] = ['warning', 'Notification options are disabled currently'];
            return to_route('admin.dashboard')->withNotify($notify);
        }

        return (new UserNotificationSender())->notificationToAll($request, ucfirst(strtolower($this->type)));
    }

    public function countBySegment($methodName)
    {
        return $this->model::active()->$methodName()->count();
    }

    public function list()
    {
        $query = $this->model::active();

        if (request()->search) {
            $query->where(function ($q) {
                $q->where('email', 'like', '%' . request()->search . '%')->orWhere('username', 'like', '%' . request()->search . '%');
            });
        }
        $users = $query->orderBy('id', 'desc')->paginate(getPaginate());
        return response()->json([
            'success' => true,
            'users'   => $users,
            'more'    => $users->hasMorePages(),
        ]);
    }

    public function notificationLog($id)
    {
        $user      = $this->model::findOrFail($id);
        $pageTitle = 'Notifications Sent to ' . $user->username;
        $column    = strtolower($this->type) . '_id';
        $logs      = NotificationLog::where($column, $id)->where('user_type', $this->type)->with(strtolower($this->type))->orderBy('id', 'desc')->paginate(getPaginate());
        $userType  = $this->type;
        return view('admin.reports.notification_history', compact('pageTitle', 'logs', 'user', 'userType'));
    }

}
