<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Traits\WithdrawProcess;
use App\Models\UserWithdrawMethod;
use App\Models\Withdrawal;
use App\Models\WithdrawMethod;
use Illuminate\Http\Request;

class UserWithdrawController extends Controller{
    
    use WithdrawProcess;

    public function withdraw(){
        $userMethods = UserWithdrawMethod::myWithdrawMethod()->with('withdrawMethod', 'currency')->get();
        $pageTitle = 'Withdraw Money';
        return view('Template::user.withdraw.withdraw_money', compact('pageTitle', 'userMethods'));
    }

    public function withdrawPreview(){
        $withdraw = Withdrawal::with('method', 'user')->where('trx', session()->get('wtrx'))->where('status', 0)->orderBy('id', 'desc')->firstOrFail();
        $pageTitle = 'Withdraw Preview';
        return view('Template::user.withdraw.preview', compact('pageTitle', 'withdraw'));
    }

    public function withdrawLog(Request $request){
        $pageTitle = "Withdraw Log";
        $withdraws = auth()->user()->withdrawals()->searchable(['trx'])->with(['method'])->orderBy('id','desc')->with('curr')->paginate(getPaginate());
        return view('Template::user.withdraw.log', compact('pageTitle', 'withdraws'));
    }

    public function withdrawMethods(){
        $userMethods = UserWithdrawMethod::myWithdrawMethod()->whereHas('withdrawMethod')->with('withdrawMethod', 'currency')->paginate(getPaginate());
        $pageTitle = 'Withdraw Methods';
        return view('Template::user.withdraw.methods', compact('pageTitle', 'userMethods'));
    }

    public function fileDownload($fileHash){
        $filePath = decrypt($fileHash);
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $general = gs();
        $title = slug($general->site_name) . '- attachments.' . $extension;
        $mimetype = mime_content_type($filePath);
        header('Content-Disposition: attachment; filename="' . $title);
        header("Content-Type: " . $mimetype);
        return readfile($filePath);
    }
}
