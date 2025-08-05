<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Lib\CurlRequest;
use App\Models\AdminNotification;
use App\Models\Agent;
use App\Models\ChargeLog;
use App\Models\Currency;
use App\Models\Deposit;
use App\Models\Merchant;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserLogin;
use App\Models\Withdrawal;
use App\Rules\FileTypeValidate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{

    public function dashboard()
    {
        $pageTitle = 'Dashboard';

        // User Info
        $widget['total_users']             = User::count();
        $widget['verified_users']          = User::active()->count();
        $widget['email_unverified_users']  = User::emailUnverified()->count();
        $widget['mobile_unverified_users'] = User::mobileUnverified()->count();

        $widget['total_agents']             = Agent::count();
        $widget['verified_agents']          = Agent::active()->count();
        $widget['email_unverified_agents']  = Agent::emailUnverified()->count();
        $widget['mobile_unverified_agents'] = Agent::mobileUnverified()->count();

        $widget['total_merchants']             = Merchant::count();
        $widget['verified_merchants']          = Merchant::active()->count();
        $widget['email_unverified_merchants']  = Merchant::emailUnverified()->count();
        $widget['mobile_unverified_merchants'] = Merchant::mobileUnverified()->count();

        // user Browsing, Country, Operating Log
        $userLoginData = UserLogin::where('created_at', '>=', Carbon::now()->subDays(30))->get(['browser', 'os', 'country']);

        $chart['user_browser_counter'] = $userLoginData->groupBy('browser')->map(function ($item, $key) {
            return collect($item)->count();
        });
        $chart['user_os_counter'] = $userLoginData->groupBy('os')->map(function ($item, $key) {
            return collect($item)->count();
        });
        $chart['user_country_counter'] = $userLoginData->groupBy('country')->map(function ($item, $key) {
            return collect($item)->count();
        })->sort()->reverse()->take(5);

        $deposit['total_deposit_pending']  = Deposit::pending()->count();
        $deposit['total_deposit_rejected'] = Deposit::rejected()->count();

        $deposit['total_deposit_amount'] = Deposit::where('deposits.status', Status::PAYMENT_SUCCESS)->leftJoin('currencies', 'currencies.id', '=', 'deposits.currency_id')
            ->selectRaw('SUM(amount * currencies.rate) as amount')
            ->first('amount')->amount ?? 0;

        $deposit['total_deposit_charge'] = Deposit::where('deposits.status', Status::PAYMENT_SUCCESS)->leftJoin('currencies', 'currencies.id', '=', 'deposits.currency_id')
            ->selectRaw('SUM(charge * currencies.rate) as charge')
            ->first('charge')->charge ?? 0;

        $withdrawals['total_withdraw_pending']  = Withdrawal::pending()->count();
        $withdrawals['total_withdraw_rejected'] = Withdrawal::rejected()->count();

        $withdrawals['total_withdraw_amount'] = Withdrawal::where('withdrawals.status', Status::PAYMENT_SUCCESS)->leftJoin('currencies', 'currencies.id', '=', 'withdrawals.currency_id')
            ->selectRaw('SUM(amount * currencies.rate) as amount')
            ->first('amount')->amount ?? 0;

        $withdrawals['total_withdraw_charge'] = Withdrawal::where('withdrawals.status', Status::PAYMENT_SUCCESS)->leftJoin('currencies', 'currencies.id', '=', 'withdrawals.currency_id')
            ->selectRaw('SUM(charge * currencies.rate) as charge')
            ->first('charge')->charge ?? 0;

        return view('admin.dashboard', compact('pageTitle', 'widget', 'chart', 'deposit', 'withdrawals'));
    }

    public function depositAndWithdrawReport(Request $request)
    {
        $diffInDays = Carbon::parse($request->start_date)->diffInDays(Carbon::parse($request->end_date));

        $groupBy = $diffInDays > 30 ? 'months' : 'days';
        $format  = $diffInDays > 30 ? '%M-%Y' : '%d-%M-%Y';

        if ($groupBy == 'days') {
            $dates = $this->getAllDates($request->start_date, $request->end_date);
        } else {
            $dates = $this->getAllMonths($request->start_date, $request->end_date);
        }

        $deposits = Deposit::where('deposits.status', Status::PAYMENT_SUCCESS)
            ->leftJoin('currencies', 'currencies.id', '=', 'deposits.currency_id')
            ->whereDate('deposits.created_at', '>=', $request->start_date)
            ->whereDate('deposits.created_at', '<=', $request->end_date)
            ->selectRaw('SUM(deposits.amount * currencies.rate) AS amount')
            ->selectRaw("DATE_FORMAT(deposits.created_at, '{$format}') as created_on")
            ->orderBy('deposits.id', 'DESC')
            ->groupBy('created_on')
            ->get();

        $withdrawals = Withdrawal::where('withdrawals.status', Status::PAYMENT_SUCCESS)
            ->leftJoin('currencies', 'currencies.id', '=', 'withdrawals.currency_id')
            ->whereDate('withdrawals.created_at', '>=', $request->start_date)
            ->whereDate('withdrawals.created_at', '<=', $request->end_date)
            ->selectRaw('SUM(withdrawals.amount * currencies.rate) AS amount')
            ->selectRaw("DATE_FORMAT(withdrawals.created_at, '{$format}') as created_on")
            ->orderBy('withdrawals.id', 'DESC')
            ->groupBy('created_on')
            ->get();

        $data = [];

        foreach ($dates as $date) {
            $data[] = [
                'created_on'  => $date,
                'deposits'    => getAmount($deposits->where('created_on', $date)->first()?->amount ?? 0),
                'withdrawals' => getAmount($withdrawals->where('created_on', $date)->first()?->amount ?? 0),
            ];
        }

        $data = collect($data);

        // Monthly Deposit & Withdraw Report Graph
        $report['created_on'] = $data->pluck('created_on');
        $report['data']       = [
            [
                'name' => 'Deposited',
                'data' => $data->pluck('deposits'),
            ],
            [
                'name' => 'Withdrawn',
                'data' => $data->pluck('withdrawals'),
            ],
        ];

        return response()->json($report);
    }

    public function transactionReport(Request $request)
    {

        $diffInDays = Carbon::parse($request->start_date)->diffInDays(Carbon::parse($request->end_date));

        $groupBy = $diffInDays > 30 ? 'months' : 'days';
        $format  = $diffInDays > 30 ? '%M-%Y' : '%d-%M-%Y';

        if ($groupBy == 'days') {
            $dates = $this->getAllDates($request->start_date, $request->end_date);
        } else {
            $dates = $this->getAllMonths($request->start_date, $request->end_date);
        }

        $plusTransactions = Transaction::leftJoin('currencies', 'currencies.id', '=', 'transactions.currency_id')
            ->where('trx_type', '+')
            ->whereDate('transactions.created_at', '>=', $request->start_date)
            ->whereDate('transactions.created_at', '<=', $request->end_date)
            ->selectRaw('SUM(transactions.amount * currencies.rate) AS amount')
            ->selectRaw("DATE_FORMAT(transactions.created_at, '{$format}') as created_on")
            ->orderBy('transactions.id', 'DESC')
            ->groupBy('created_on')
            ->get();

        $minusTransactions = Transaction::leftJoin('currencies', 'currencies.id', '=', 'transactions.currency_id')
            ->where('trx_type', '-')
            ->whereDate('transactions.created_at', '>=', $request->start_date)
            ->whereDate('transactions.created_at', '<=', $request->end_date)
            ->selectRaw('SUM(transactions.amount * currencies.rate) AS amount')
            ->selectRaw("DATE_FORMAT(transactions.created_at, '{$format}') as created_on")
            ->orderBy('transactions.id', 'DESC')
            ->groupBy('created_on')
            ->get();

        $data = [];

        foreach ($dates as $date) {
            $data[] = [
                'created_on' => $date,
                'credits'    => getAmount($plusTransactions->where('created_on', $date)->first()?->amount ?? 0),
                'debits'     => getAmount($minusTransactions->where('created_on', $date)->first()?->amount ?? 0),
            ];
        }

        $data = collect($data);

        $report['created_on'] = $data->pluck('created_on');
        $report['data']       = [
            [
                'name' => 'Plus Transactions',
                'data' => $data->pluck('credits'),
            ],
            [
                'name' => 'Minus Transactions',
                'data' => $data->pluck('debits'),
            ],
        ];

        return response()->json($report);
    }

    public function chargeCommissionReport(Request $request)
    {
        $diffInDays = Carbon::parse($request->start_date)->diffInDays(Carbon::parse($request->end_date));

        $groupBy = $diffInDays > 30 ? 'months' : 'days';
        $format  = $diffInDays > 30 ? '%M-%Y' : '%d-%M-%Y';

        if ($groupBy == 'days') {
            $dates = $this->getAllDates($request->start_date, $request->end_date);
        } else {
            $dates = $this->getAllMonths($request->start_date, $request->end_date);
        }

        $charges = ChargeLog::where('charge_logs.remark', '!=', 'add_money')->where('charge_logs.remark', '!=', 'withdraw')
            ->where('charge_logs.remark', '!=', 'commission')
            ->leftJoin('currencies', 'currencies.id', '=', 'charge_logs.currency_id')
            ->whereDate('charge_logs.created_at', '>=', $request->start_date)
            ->whereDate('charge_logs.created_at', '<=', $request->end_date)
            ->selectRaw("SUM(amount * currencies.rate) as chareAmount")
            ->selectRaw("DATE_FORMAT(charge_logs.created_at, '{$format}') as created_on")
            ->orderBy('charge_logs.id', 'DESC')
            ->groupBy('created_on')
            ->get();

        $commissions = ChargeLog::where('charge_logs.remark', 'commission')
            ->leftJoin('currencies', 'currencies.id', '=', 'charge_logs.currency_id')
            ->whereDate('charge_logs.created_at', '>=', $request->start_date)
            ->whereDate('charge_logs.created_at', '<=', $request->end_date)
            ->selectRaw("SUM(amount * currencies.rate) as commissionAmount")
            ->selectRaw("DATE_FORMAT(charge_logs.created_at, '{$format}') as created_on")
            ->orderBy('charge_logs.id', 'DESC')
            ->groupBy('created_on')
            ->get();

        $data = [];

        foreach ($dates as $date) {
            $data[] = [
                'created_on' => $date,
                'charge'     => getAmount($charges->where('created_on', $date)->first()?->chareAmount ?? 0),
                'commission' => getAmount($commissions->where('created_on', $date)->first()?->commissionAmount ?? 0),
            ];
        }

        $data = collect($data);

        $report['created_on'] = $data->pluck('created_on');
        $report['data']       = [
            [
                'name' => 'Charges',
                'data' => $data->pluck('charge'),
            ],
            [
                'name' => 'Commissions',
                'data' => $data->pluck('commission'),
            ],
        ];

        return response()->json($report);
    }

    public function registrationReport(Request $request)
    {
        $diffInDays = Carbon::parse($request->start_date)->diffInDays(Carbon::parse($request->end_date));

        $groupBy = $diffInDays > 30 ? 'months' : 'days';
        $format  = $diffInDays > 30 ? '%M-%Y' : '%d-%M-%Y';

        if ($groupBy == 'days') {
            $dates = $this->getAllDates($request->start_date, $request->end_date);
        } else {
            $dates = $this->getAllMonths($request->start_date, $request->end_date);
        }

        $userRegistrations = User::whereDate('created_at', '>=', $request->start_date)
            ->whereDate('created_at', '<=', $request->end_date)
            ->selectRaw('COUNT(*) AS count')
            ->selectRaw("DATE_FORMAT(created_at, '{$format}') as created_on")
            ->latest()
            ->groupBy('created_on')
            ->get();

        $agentRegistrations = Agent::whereDate('created_at', '>=', $request->start_date)
            ->whereDate('created_at', '<=', $request->end_date)
            ->selectRaw('COUNT(*) AS count')
            ->selectRaw("DATE_FORMAT(created_at, '{$format}') as created_on")
            ->latest()
            ->groupBy('created_on')
            ->get();

        $merchantRegistrations = Merchant::whereDate('created_at', '>=', $request->start_date)
            ->whereDate('created_at', '<=', $request->end_date)
            ->selectRaw('COUNT(*) AS count')
            ->selectRaw("DATE_FORMAT(created_at, '{$format}') as created_on")
            ->latest()
            ->groupBy('created_on')
            ->get();

        $data = [];

        foreach ($dates as $date) {
            $data[] = [
                'created_on'  => $date,
                'userReg'     => $userRegistrations->where('created_on', $date)->first()?->count ?? 0,
                'agentReg'    => $agentRegistrations->where('created_on', $date)->first()?->count ?? 0,
                'merchantReg' => $merchantRegistrations->where('created_on', $date)->first()?->count ?? 0,
            ];
        }

        $data = collect($data);

        $report['created_on'] = $data->pluck('created_on');
        $report['data']       = [
            [
                'name' => 'User Registration',
                'data' => $data->pluck('userReg'),
            ],
            [
                'name' => 'Agent Registration',
                'data' => $data->pluck('agentReg'),
            ],
            [
                'name' => 'Merchant Registration',
                'data' => $data->pluck('merchantReg'),
            ],
        ];

        return response()->json($report);
    }

    private function getAllDates($startDate, $endDate)
    {
        $dates       = [];
        $currentDate = new \DateTime($startDate);
        $endDate     = new \DateTime($endDate);

        while ($currentDate <= $endDate) {
            $dates[] = $currentDate->format('d-F-Y');
            $currentDate->modify('+1 day');
        }

        return $dates;
    }

    private function getAllMonths($startDate, $endDate)
    {
        if ($endDate > now()) {
            $endDate = now()->format('Y-m-d');
        }

        $startDate = new \DateTime($startDate);
        $endDate   = new \DateTime($endDate);

        $months = [];

        while ($startDate <= $endDate) {
            $months[] = $startDate->format('F-Y');
            $startDate->modify('+1 month');
        }

        return $months;
    }

    public function profile()
    {
        $pageTitle = 'Profile';
        $admin     = auth('admin')->user();
        return view('admin.profile', compact('pageTitle', 'admin'));
    }

    public function profileUpdate(Request $request)
    {
        $request->validate([
            'name'  => 'required',
            'email' => 'required|email',
            'image' => ['nullable', 'image', new FileTypeValidate(['jpg', 'jpeg', 'png'])],
        ]);
        $user = auth('admin')->user();

        if ($request->hasFile('image')) {
            try {
                $old         = $user->image;
                $user->image = fileUploader($request->image, getFilePath('adminProfile'), getFileSize('adminProfile'), $old);
            } catch (\Exception $exp) {
                $notify[] = ['error', 'Couldn\'t upload your image'];
                return back()->withNotify($notify);
            }
        }

        $user->name  = $request->name;
        $user->email = $request->email;
        $user->save();
        $notify[] = ['success', 'Profile updated successfully'];
        return to_route('admin.profile')->withNotify($notify);
    }

    public function password()
    {
        $pageTitle = 'Password Setting';
        $admin     = auth('admin')->user();
        return view('admin.password', compact('pageTitle', 'admin'));
    }

    public function passwordUpdate(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'password'     => 'required|min:5|confirmed',
        ]);

        $user = auth('admin')->user();
        if (!Hash::check($request->old_password, $user->password)) {
            $notify[] = ['error', 'Password doesn\'t match!!'];
            return back()->withNotify($notify);
        }
        $user->password = Hash::make($request->password);
        $user->save();
        $notify[] = ['success', 'Password changed successfully.'];
        return to_route('admin.password')->withNotify($notify);
    }

    public function notifications()
    {
        $notifications   = AdminNotification::orderBy('id', 'desc')->with('user')->paginate(getPaginate());
        $hasUnread       = AdminNotification::where('is_read', Status::NO)->exists();
        $hasNotification = AdminNotification::exists();
        $pageTitle       = 'Notifications';
        return view('admin.notifications', compact('pageTitle', 'notifications', 'hasUnread', 'hasNotification'));
    }

    public function notificationRead($id)
    {
        $notification          = AdminNotification::findOrFail($id);
        $notification->is_read = Status::YES;
        $notification->save();
        $url = $notification->click_url;
        if ($url == '#') {
            $url = url()->previous();
        }
        return redirect($url);
    }

    public function requestReport()
    {
        $pageTitle            = 'Your Listed Report & Request';
        $arr['app_name']      = systemDetails()['name'];
        $arr['app_url']       = env('APP_URL');
        $arr['purchase_code'] = env('PURCHASECODE');
        $url                  = "https://license.viserlab.com/issue/get?" . http_build_query($arr);
        $response             = CurlRequest::curlContent($url);
        $response             = json_decode($response);
        if (!$response || !@$response->status || !@$response->message) {
            return to_route('admin.dashboard')->withErrors('Something went wrong');
        }
        if ($response->status == 'error') {
            return to_route('admin.dashboard')->withErrors($response->message);
        }
        $reports = $response->message[0];
        return view('admin.reports', compact('reports', 'pageTitle'));
    }

    public function reportSubmit(Request $request)
    {
        $request->validate([
            'type'    => 'required|in:bug,feature',
            'message' => 'required',
        ]);
        $url = 'https://license.viserlab.com/issue/add';

        $arr['app_name']      = systemDetails()['name'];
        $arr['app_url']       = env('APP_URL');
        $arr['purchase_code'] = env('PURCHASECODE');
        $arr['req_type']      = $request->type;
        $arr['message']       = $request->message;
        $response             = CurlRequest::curlPostContent($url, $arr);
        $response             = json_decode($response);
        if (!$response || !@$response->status || !@$response->message) {
            return to_route('admin.dashboard')->withErrors('Something went wrong');
        }
        if ($response->status == 'error') {
            return back()->withErrors($response->message);
        }
        $notify[] = ['success', $response->message];
        return back()->withNotify($notify);
    }

    public function readAllNotification()
    {
        AdminNotification::where('is_read', Status::NO)->update([
            'is_read' => Status::YES,
        ]);
        $notify[] = ['success', 'Notifications read successfully'];
        return back()->withNotify($notify);
    }

    public function deleteAllNotification()
    {
        AdminNotification::truncate();
        $notify[] = ['success', 'Notifications deleted successfully'];
        return back()->withNotify($notify);
    }

    public function deleteSingleNotification($id)
    {
        AdminNotification::where('id', $id)->delete();
        $notify[] = ['success', 'Notification deleted successfully'];
        return back()->withNotify($notify);
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

    public function trxDetailGraph(Request $request)
    {
        $pageTitle = "Transaction Detail Graph";

        $request->merge([
            'currency_code' => strtoupper($request->currency_code),
            'user_type'     => strtoupper($request->user_type),
        ]);

        $dateSearch = $request->date;
        $start      = null;
        $end        = null;

        if ($dateSearch) {
            $date = explode('-', $dateSearch);

            $request->merge([
                'start_date' => trim(@$date[0]),
                'end_date'   => trim(@$date[1]),
            ]);
            $request->validate([
                'start_date' => 'required',
                'end_date'   => 'nullable',
            ]);

            $start = showDateTime(@$date[0], 'Y-m-d');
            $end   = showDateTime(@str_replace(' ', '', @$date[1]), 'Y-m-d');
        }

        $report['trx_dates']  = collect([]);
        $report['trx_amount'] = collect([]);

        $currencies = Currency::get(['currency_code', 'id']);

        $transactions = Transaction::where('transactions.created_at', '>=', Carbon::now()->subYear())
            ->where('transactions.trx_type', '+')
            ->leftjoin('currencies', 'currencies.id', '=', 'transactions.currency_id')
            ->selectRaw("DATE_FORMAT(transactions.created_at,'%dth-%M') as dates")
            ->when($dateSearch, function ($q) use ($start, $end) {
                return $q->whereBetween('transactions.created_at', [$start, $end]);
            })
            ->searchable(['user_type'])
            ->filter(['currency:currency_code'])
            ->selectRaw("SUM(amount * currencies.rate) as totalAmount")
            ->orderBy('transactions.created_at')
            ->groupBy('dates')
            ->get();

        $transactions->map(function ($trxData) use ($report) {
            $report['trx_dates']->push($trxData->dates);
            $report['trx_amount']->push($trxData->totalAmount);
        });

        return view('admin.transaction_detail_graph', compact('report', 'pageTitle', 'currencies', 'dateSearch'));
    }

}
