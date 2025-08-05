<?php

namespace App\Traits;

use App\Models\Deposit;
use App\Models\QRcode;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\Withdrawal;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

trait UserPartials
{

    protected function user()
    {
        return userGuard()['user'];
    }

    public function createQrCode()
    {
        $user   = $this->user();
        $qrCode = $user->qrCode;

        if (!$qrCode) {
            $qrCode              = new QRcode();
            $qrCode->user_id     = $user->id;
            $qrCode->user_type   = userGuard()['type'];
            $qrCode->unique_code = keyGenerator(15);
            $qrCode->save();
        }
        return $qrCode;
    }

    public function downLoadQrCode()
    {
        $user    = $this->user();
        $qrCode  = $user->qrCode()->first();
        $general = gs();

        $file     = cryptoQR($qrCode->unique_code);
        $filename = $qrCode->unique_code . '.jpg';

        $manager  = new ImageManager(new Driver());
        $template = $manager->read('assets/images/qr_code_template/' . $general->qr_code_template);

        $client   = new Client();
        $response = $client->get($file);
        $imageContent = $response->getBody()->getContents();

        $qrCode = $manager->read($imageContent)->cover(2000, 2000);
        $template->place($qrCode, 'center');
        $image   = $template->encode();
        $headers = [
            'Content-Type'        => 'image/jpeg',
            'Content-Disposition' => 'attachment; filename=' . $filename,
        ];

        return response()->stream(function () use ($image) {
            echo $image;
        }, 200, $headers);
    }

    public function topTransactedWallets()
    {
        $wallets = Wallet::hasCurrency()->where('user_id', $this->user()->id)->where('user_type', userGuard()['type'])
            ->select(DB::raw('*'))
            ->addSelect(DB::raw('
                (select count(*)
                from transactions
                where wallet_id = wallets.id)
                as transactions
        '))->orderBy('transactions', 'desc');

        return $wallets;
    }

    public function moneyInOut($day = 7)
    {
        $user     = $this->user();
        $date     = Carbon::today()->subDays($day);
        $moneyIn  = $user->transactions()->where('trx_type', '+')->whereDate('created_at', '>=', $date)->with('currency')->get(['amount', 'currency_id']);
        $moneyOut = $user->transactions()->where('trx_type', '-')->whereDate('created_at', '>=', $date)->with('currency')->get(['amount', 'currency_id']);

        $totalMoneyIn  = 0;
        $totalMoneyOut = 0;

        $in = [];
        foreach ($moneyIn as $inTrx) {
            $in[] = $inTrx->amount * $inTrx->currency->rate;
        }
        $totalMoneyIn = array_sum($in);

        $out = [];
        foreach ($moneyOut as $outTrx) {
            $out[] = $outTrx->amount * $outTrx->currency->rate;
        }

        $totalMoneyOut = array_sum($out);

        return ['totalMoneyIn' => $totalMoneyIn, 'totalMoneyOut' => $totalMoneyOut];
    }

    public function kycStyle()
    {

        $user = $this->user();
        $type = strtolower(userGuard()['type']);
        $kyc  = null;

        if ($user->kv == 0) {
            $kyc['bgColor'] = '';
            $kyc['btnBg']   = '';
            $kyc['btnTxt']  = 'SUBMIT NOW';
            $kyc['iconBg']  = 'text--base';
            $kyc['msg']     = "You have information to submit in verification center.";
            $kyc['route']   = route($type . '.kyc.form');
        } elseif ($user->kv == 2) {
            $kyc['bgColor'] = 'bg--warning';
            $kyc['btnBg']   = 'bg--primary';
            $kyc['btnTxt']  = 'PENDING';
            $kyc['iconBg']  = 'text--primary';
            $kyc['msg']     = "Your information has been submitted for review.";
            $kyc['route']   = route($type . '.kyc.data');
        }

        return $kyc;
    }

    public function trxLimit($type)
    {
        $user = $this->user();

        $transactions = $user->transactions()->leftJoin('currencies', 'currencies.id', '=', 'transactions.currency_id')->where('user_type', userGuard()['type'])
            ->where('remark', $type)
            ->selectRaw("SUM(amount * currencies.rate) as totalAmount");

        if ($type == 'request_money') {
            $transactions = $transactions->where('trx_type', '-');
        }

        return [
            'daily'   => $transactions->whereDate('transactions.created_at', Carbon::now())->get()->sum('totalAmount'),
            'monthly' => $transactions->whereMonth('transactions.created_at', Carbon::now())->get()->sum('totalAmount'),
        ];
    }

    public function trxLog($request)
    {

        $search    = $request->search;
        $type      = $request->type;
        $operation = $request->operation;

        if ($type && $type == 'plus_trx') {
            $type = '+';
        } elseif ($type && $type == 'minus_trx') {
            $type = '-';
        }

        $time = $request->time;
        if ($time) {
            if ($time == '7days') {
                $time = 7;
            } elseif ($time == '15days') {
                $time = 15;
            } elseif ($time == '1month') {
                $time = 31;
            } elseif ($time == '1year') {
                $time = 365;
            }

        }

        $currency = strtoupper($request->currency);

        $histories = Transaction::where('user_id', $this->user()->id)->where('user_type', userGuard()['type'])
            ->when($search, function ($trx, $search) {
                return $trx->where('trx', $search);
            })
            ->when($type, function ($trx, $type) {
                return $trx->where('trx_type', $type);
            })
            ->when($time, function ($trx, $time) {
                return $trx->where('created_at', '>=', Carbon::today()->subDays($time));
            })
            ->when($operation, function ($trx, $operation) {
                return $trx->where('remark', $operation);
            })
            ->when($currency, function ($trx, $currency) {
                return $trx->whereHas('currency', function ($curr) use ($currency) {
                    $curr->where('currency_code', $currency);
                });
            })
            ->with(['currency', 'receiverUser', 'receiverAgent', 'receiverMerchant'])->orderBy('id', 'DESC')->paginate(getPaginate());

        return $histories;
    }

    public function totalDeposit()
    {
        $log = Deposit::where('user_type', userGuard()['type'])
            ->where('user_id', $this->user()->id)->where('deposits.status', 1)
            ->leftJoin('currencies', 'currencies.id', '=', 'deposits.currency_id')
            ->with('currency')->selectRaw('SUM(amount * currencies.rate) as finalAmount')
            ->first();
        return $log->finalAmount;
    }

    public function totalWithdraw()
    {
        $log = Withdrawal::where('user_type', userGuard()['type'])
            ->where('user_id', $this->user()->id)
            ->where('withdrawals.status', 1)
            ->with('curr')
            ->leftJoin('currencies', 'currencies.id', '=', 'withdrawals.currency_id')
            ->selectRaw('SUM(amount * currencies.rate) as finalAmount')->first();
        return $log->finalAmount;
    }

    public function trxGraph()
    {
        // Transaction Graph
        $report['trx_dates']  = collect([]);
        $report['trx_amount'] = collect([]);

        $transactions = Transaction::where('user_type', userGuard()['type'])->where('user_id', $this->user()->id)->where('transactions.created_at', '>=', Carbon::now()->subYear())
            ->where('trx_type', '+')
            ->leftJoin('currencies', 'currencies.id', '=', 'transactions.currency_id')
            ->selectRaw("SUM(amount * currencies.rate) as totalAmount")
            ->selectRaw("DATE_FORMAT(transactions.created_at,'%M-%Y') as dates")
            ->orderBy('transactions.created_at')->groupBy('dates')->get();

        $transactions->map(function ($trxData) use ($report) {
            $report['trx_dates']->push($trxData->dates);
            $report['trx_amount']->push($trxData->totalAmount);
        });

        return $report;
    }

}
