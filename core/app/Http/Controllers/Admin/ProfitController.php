<?php

namespace App\Http\Controllers\Admin;

use App\Models\Currency;
use App\Models\ChargeLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProfitController extends Controller{

    public function allProfit(Request $request){
        
        $pageTitle = "All Profit Logs";
        $totalProfit = totalProfit(); 
        $currencies = Currency::enable()->get(['id','currency_code']);
        
        $logs = $this->getData('all');
        $search = $request->search;

        return view('admin.reports.profit_logs',compact('pageTitle','logs','totalProfit', 'currencies', 'search'));
    }

    public function onlyProfit(Request $request){

        $pageTitle = "Only Profits";
        $totalProfit = totalProfit();
        $currencies = Currency::enable()->get(['id','currency_code']);

        $logs = $this->getData('profit');
        $currency = $request->currency;

        return view('admin.reports.profit_logs',compact('pageTitle','logs','totalProfit', 'currencies', 'currency'));
    }

    public function profitCommission(Request $request){

        $pageTitle = "Commission Logs";
        $totalProfit = totalProfit();
        $currencies = Currency::enable()->get(['id','currency_code']);

        $logs = $this->getData('commission');
        $currency = $request->currency;

        return view('admin.reports.profit_logs',compact('pageTitle','logs','totalProfit', 'currencies', 'currency'));
    }

    protected function getData($scope = null){  
        
        $logs = ChargeLog::with(['currency','user','agent','merchant']);
 
        if($scope == 'all'){ 
            $logs = $logs->where('remark', '!=', 'add_money')->where('remark', '!=', 'withdraw');
        }
        elseif($scope == 'profit'){
            $logs = $logs->where('remark', '!=', 'add_money')->where('remark', '!=', 'withdraw')->where('remark', null);
        }
        elseif($scope == 'commission'){
            $logs = $logs->where('remark', '!=', 'add_money')->where('remark', '!=', 'withdraw')->where('remark','!=', null);
        }

        $logs = $logs->orderBy('id','DESC')->filter(['currency:currency_code'])->dateFilter()->paginate(getPaginate());
        return $logs;
    }

    public function profitExportCsv(Request $request){
      
        $logs = $this->getData($request->scope);
        $fileName = 'profit_logs_'.showDateTime(now(),'d_m_Y').'.csv';

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Trx', 'User', 'User Type', 'Amount', 'Currency', 'Remark', 'Time & Date'];

        $callback = function() use ($logs, $columns) {

            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($logs as $log) {
                if($log->user){
                    $user = $log->user->username;
                }
                elseif($log->agent){
                    $user = $log->agent->username;
                }
                else{
                    $user = $log->merchant->username;
                }

                $row['Trx']             = $log->trx;
                $row['User']            = $user;
                $row['User Type']       = $log->user_type;
                $row['Amount']          = $log->amount;
                $row['Currency']        = $log->currency->currency_code;
                $row['Remark']          = $log->remark ? ucfirst(str_replace('_',' ',$log->remark)) : 'N/A';
                $row['Time & Date']     = showDateTime($log->created_at, ' d M-Y');
             
                fputcsv($file, 
                    array($row['Trx'], $row['User'], $row['User Type'], $row['Amount'], $row['Currency'], $row['Remark'], $row['Time & Date'])
                );
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

}
 