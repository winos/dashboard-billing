<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Traits\WithdrawProcess;
use App\Models\UserWithdrawMethod;
use App\Models\Withdrawal;

class AgentWithdrawController extends Controller{

    use WithdrawProcess;

    public function withdraw(){        
        $userMethods = UserWithdrawMethod::myWithdrawMethod()->with('withdrawMethod', 'currency')->get();
        $pageTitle = 'Withdraw Money';
        return view('Template::agent.withdraw.withdraw_money', compact('pageTitle', 'userMethods'));
    }

    public function withdrawMethods(){
        $userMethods = UserWithdrawMethod::myWithdrawMethod()->with('withdrawMethod', 'currency')->paginate(getPaginate());
        $pageTitle = 'Withdraw Methods';
        return view('Template::agent.withdraw.methods', compact('pageTitle', 'userMethods'));
    }

    public function withdrawPreview(){  
        $withdraw = Withdrawal::with('method', 'agent')->where('trx', session()->get('wtrx'))->where('status', 0)->orderBy('id', 'desc')->firstOrFail();
        $pageTitle = 'Withdraw Preview'; 
        return view('Template::agent.withdraw.preview', compact('pageTitle', 'withdraw'));
    }

    public function withdrawLog(){   
        $pageTitle = "Withdraw Log";
        $user = userGuard()['user'];

        $withdraws = $user->withdrawals()->with('method', 'curr')
            ->whereHas('method')->orderBy('id', 'desc')
        ->paginate(getPaginate());

        return view('Template::agent.withdraw.log', compact('pageTitle', 'withdraws'));
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
