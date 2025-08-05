<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Withdrawal;
use Illuminate\Http\Request;

class WithdrawalController extends Controller
{
    public function pending($userId = null)
    {
        $pageTitle   = 'Pending Withdrawals';
        $withdrawals = $this->withdrawalData('pending', userId: $userId);
        return view('admin.withdraw.withdrawals', compact('pageTitle', 'withdrawals'));
    }

    public function approved($userId = null)
    {
        $pageTitle   = 'Approved Withdrawals';
        $withdrawals = $this->withdrawalData('approved', userId: $userId);
        return view('admin.withdraw.withdrawals', compact('pageTitle', 'withdrawals'));
    }

    public function rejected($userId = null)
    {
        $pageTitle   = 'Rejected Withdrawals';
        $withdrawals = $this->withdrawalData('rejected', userId: $userId);
        return view('admin.withdraw.withdrawals', compact('pageTitle', 'withdrawals'));
    }

    public function all($userId = null)
    {
        $pageTitle      = 'All Withdrawals';
        $withdrawalData = $this->withdrawalData($scope = null, $summary = true, userId: $userId);
        $withdrawals    = $withdrawalData['data'];
        $summary        = $withdrawalData['summary'];
        $successful     = $summary['successful'];
        $pending        = $summary['pending'];
        $rejected       = $summary['rejected'];

        return view('admin.withdraw.withdrawals', compact('pageTitle', 'withdrawals', 'successful', 'pending', 'rejected'));
    }

    protected function withdrawalData($scope = null, $summary = false, $userId = null)
    {
        $response = $this->typeFormat(); 
        $with = array_merge($response['with'], ['method', 'curr']);

        if ($scope) {
            $withdrawals = Withdrawal::$scope()->with($with);
        } else {
            $withdrawals = Withdrawal::query();
        }

        if($response['type'] != '*'){
            $withdrawals = $withdrawals->where('user_type', $response['type']);
        }

        if ($userId) {
            $withdrawals = $withdrawals->where('user_id', $userId);
        }

        $request = request();
        $request->merge(['user_type'=> strtoupper($request->user_type)]);

        if($request->search){
            $withdrawals = $withdrawals->where(function($q)  use ($request, $response) {
                foreach($response['with'] as $relation){ 
                    $q->orWhereHas($relation, function ($query) use ($request, $relation) {
                        $query->where('username', 'like',"%$request->search%")->where('user_type', strtoupper($relation));
                    });
                }
            })->orWhere('trx','LIKE',"%$request->search%");
        }

        $withdrawals = $withdrawals->filter(['user_type', 'curr:currency_id'])->dateFilter(table: 'withdrawals');

        if ($request->method) {
            $withdrawals = $withdrawals->where('method_id', $request->method);
        }
        if (!$summary) {
            return $withdrawals->where('status', '!=', Status::PAYMENT_INITIATE)->orderBy('id', 'desc')->paginate(getPaginate());
        } else {

            $successful = clone $withdrawals;
            $pending    = clone $withdrawals;
            $rejected   = clone $withdrawals;

            $successfulSummary = $successful->where('withdrawals.status', Status::PAYMENT_SUCCESS)
                ->leftJoin('currencies', 'currencies.id', '=', 'withdrawals.currency_id')
                ->selectRaw("SUM(amount * currencies.rate) as amount")
                ->first('amount')->amount ?? 0;

            $pendingSummary = $pending->where('withdrawals.status', Status::PAYMENT_PENDING)
                ->leftJoin('currencies', 'currencies.id', '=', 'withdrawals.currency_id')
                ->selectRaw("SUM(amount * currencies.rate) as amount")
                ->first('amount')->amount ?? 0;

            $rejectedSummary = $rejected->where('withdrawals.status', Status::PAYMENT_REJECT)
                ->leftJoin('currencies', 'currencies.id', '=', 'withdrawals.currency_id')
                ->selectRaw("SUM(amount * currencies.rate) as amount")
                ->first('amount')->amount ?? 0;

            return [
                'data'    => $withdrawals->where('status', '!=', Status::PAYMENT_INITIATE)->orderBy('id', 'desc')->paginate(getPaginate()),
                'summary' => [
                    'successful' => $successfulSummary,
                    'pending'    => $pendingSummary,
                    'rejected'   => $rejectedSummary,
                ],
            ];
        }
    }

    protected function typeFormat(){
        
        $userType = @request()->user_type;
        $userType = strtolower(@$userType);

        $array = [
            'user'=> ['type'=>'USER', 'with'=>['user']],
            'agent'=> ['type'=>'AGENT', 'with'=>['agent']],
            'merchant'=> ['type'=>'MERCHANT', 'with'=>['merchant']],
            'all'=> ['type'=>'*', 'with'=>['user', 'agent', 'merchant']]
        ];

        return @$array[$userType] ?? @$array['all'];
    }

    public function details($id)
    {
        $withdrawal = Withdrawal::where('id', $id)->where('status', '!=', Status::PAYMENT_INITIATE)->with(['user', 'method'])->firstOrFail();
        $pageTitle = 'Withdrawal Details';
        $details    = $withdrawal->withdraw_information ? json_encode($withdrawal->withdraw_information) : null;

        return view('admin.withdraw.detail', compact('pageTitle', 'withdrawal', 'details'));
    }

    public function approve(Request $request)
    {
        $request->validate(['id' => 'required|integer']);
        $withdraw                 = Withdrawal::where('id', $request->id)->where('status', Status::PAYMENT_PENDING)->with('user')->firstOrFail();
        $withdraw->status         = Status::PAYMENT_SUCCESS;
        $withdraw->admin_feedback = $request->details;
        $withdraw->save();

        notify($withdraw->user, 'WITHDRAW_APPROVE', [
            'method_name'     => $withdraw->method->name,
            'method_currency' => $withdraw->currency,
            'method_amount'   => showAmount($withdraw->final_amount, currencyFormat: false),
            'amount'          => showAmount($withdraw->amount, currencyFormat: false),
            'charge'          => showAmount($withdraw->charge, currencyFormat: false),
            'rate'            => showAmount($withdraw->rate, currencyFormat: false),
            'trx'             => $withdraw->trx,
            'admin_details'   => $request->details,
        ]);

        $notify[] = ['success', 'Withdrawal approved successfully'];
        return to_route('admin.withdraw.data.pending')->withNotify($notify);
    }

    public function reject(Request $request)
    {
        $request->validate(['id' => 'required|integer']);
        $withdraw = Withdrawal::where('id', $request->id)->where('status', Status::PAYMENT_PENDING)->with('user')->firstOrFail();

        $withdraw->status         = Status::PAYMENT_REJECT;
        $withdraw->admin_feedback = $request->details;
        $withdraw->save();

        $user = $withdraw->user;
        $user->balance += $withdraw->amount;
        $user->save();

        $transaction               = new Transaction();
        $transaction->user_id      = $withdraw->user_id;
        $transaction->amount       = $withdraw->amount;
        $transaction->post_balance = $user->balance;
        $transaction->charge       = 0;
        $transaction->trx_type     = '+';
        $transaction->remark       = 'withdraw_reject';
        $transaction->details      = 'Refunded for withdrawal rejection';
        $transaction->trx          = $withdraw->trx;
        $transaction->save();

        notify($user, 'WITHDRAW_REJECT', [
            'method_name'     => $withdraw->method->name,
            'method_currency' => $withdraw->currency,
            'method_amount'   => showAmount($withdraw->final_amount, currencyFormat: false),
            'amount'          => showAmount($withdraw->amount, currencyFormat: false),
            'charge'          => showAmount($withdraw->charge, currencyFormat: false),
            'rate'            => showAmount($withdraw->rate, currencyFormat: false),
            'trx'             => $withdraw->trx,
            'post_balance'    => showAmount($user->balance, currencyFormat: false),
            'admin_details'   => $request->details,
        ]);

        $notify[] = ['success', 'Withdrawal rejected successfully'];
        return to_route('admin.withdraw.data.pending')->withNotify($notify);
    }

}
