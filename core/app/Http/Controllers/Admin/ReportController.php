<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationLog;
use App\Models\Transaction;
use App\Models\UserLogin;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function transaction(Request $request, $userId = null)
    {
        $pageTitle = 'Transaction Logs';
        $remarks   = Transaction::distinct('remark')->orderBy('remark')->get('remark');

        $response = $this->typeFormat();

        $transactions = Transaction::filter(['trx_type', 'remark', 'user_type', 'currency:currency_id'])->dateFilter();

        if ($request->search) {
            $transactions = $transactions->where(function ($q) use ($request, $response) {
                foreach ($response['with'] as $relation) {
                    $q->orWhereHas($relation, function ($query) use ($request, $relation) {
                        $query->where('username', 'like', "%$request->search%")->where('user_type', strtoupper($relation));
                    });
                }
            })->orWhere('trx', 'LIKE', "%$request->search%");
        }

        $transactions = $transactions->orderBy('id', 'desc')->with('wallet', 'currency', 'receiverUser', 'receiverAgent', 'receiverMerchant');

        if ($userId) {
            $transactions = $transactions->where('user_id', $userId);
        }

        $transactions = $transactions->paginate(getPaginate());

        return view('admin.reports.transactions', compact('pageTitle', 'transactions', 'remarks'));
    }

    public function loginHistory(Request $request)
    {
        $request->merge(['user_type' => strtolower($request->user_type)]);
        $response = $this->typeFormat();

        $loginLogs = UserLogin::where($response['column'], '!=', 0)->with($response['with'])->dateFilter();

        if ($request->user_type == 'user' || $request->user_type == 'agent' || $request->user_type == 'merchant') {
            $loginLogs = $loginLogs->whereHas($request->user_type);
        }

        $pageTitle = 'Login History';

        if ($request->search) {
            $search = $request->search;
            $pageTitle .= ' - ' . $search;

            $loginLogs = $loginLogs->where(function ($query) use ($response, $request) {
                foreach ($response['with'] as $relation) {
                    $query->orWhereHas($relation, function ($query2) use ($request) {
                        $query2->where('username', 'like', "%$request->search%");
                    });
                }
            });
        }

        $loginLogs = $loginLogs->orderBy('id', 'desc')->paginate(getPaginate());
        return view('admin.reports.logins', compact('pageTitle', 'loginLogs'));
    }

    public function loginIpHistory($ip)
    {
        $pageTitle = 'Login by - ' . $ip;
        $loginLogs = UserLogin::where('user_ip', $ip)->orderBy('id', 'desc')->with('user')->paginate(getPaginate());
        return view('admin.reports.logins', compact('pageTitle', 'loginLogs', 'ip'));
    }

    public function notificationHistory(Request $request)
    {

        $request->merge(['user_type' => strtoupper($request->user_type)]);

        $pageTitle = 'Notification History';
        $logs      = NotificationLog::orderBy('id', 'desc');

        $response = $this->typeFormat();
        $search   = $request->search;

        if ($search) {
            foreach ($response['with'] as $relation) {
                $logs = $logs->orWhereHas($relation, function ($query) use ($request, $relation) {
                    $query->where('username', 'like', "%$request->search%")->where('user_type', strtoupper($relation));
                });
            }
        }

        $logs = $logs->filter(['user_type'])->dateFilter()->with(['user', 'agent', 'merchant'])->paginate(getPaginate());
        return view('admin.reports.notification_history', compact('pageTitle', 'logs'));
    }

    public function emailDetails($id)
    {
        $pageTitle = 'Email Details';
        $email     = NotificationLog::findOrFail($id);
        return view('admin.reports.email_details', compact('pageTitle', 'email'));
    }

    protected function typeFormat()
    {

        $userType = @request()->user_type;
        $userType = strtolower(@$userType);

        $array = [
            'user'     => ['column' => 'user_id', 'with' => ['user']],
            'agent'    => ['column' => 'agent_id', 'with' => ['agent']],
            'merchant' => ['column' => 'merchant_id', 'with' => ['merchant']],
            'all'      => ['column' => 'id', 'with' => ['user', 'agent', 'merchant']],
        ];

        return @$array[@$userType] ?? @$array['all'];
    }

}
