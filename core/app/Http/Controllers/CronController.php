<?php

namespace App\Http\Controllers;

use App\Constants\Status;
use App\Lib\CurlRequest;
use App\Models\CronJob;
use App\Models\CronJobLog;
use App\Models\Currency;
use Carbon\Carbon;

class CronController extends Controller
{
    public function cron()
    {
        $general            = gs();
        $general->last_cron = now();
        $general->save();

        $crons = CronJob::with('schedule');

        if (request()->alias) {
            $crons->where('alias', request()->alias);
        } else {
            $crons->where('next_run', '<', now())->where('is_running', Status::YES);
        }
        $crons = $crons->get();
        foreach ($crons as $cron) {
            $cronLog              = new CronJobLog();
            $cronLog->cron_job_id = $cron->id;
            $cronLog->start_at    = now();
            if ($cron->is_default) {
                $controller = new $cron->action[0];
                try {
                    $method = $cron->action[1];
                    $controller->$method();
                } catch (\Exception $e) {
                    $cronLog->error = $e->getMessage();
                }
            } else {
                try {
                    CurlRequest::curlContent($cron->url);
                } catch (\Exception $e) {
                    $cronLog->error = $e->getMessage();
                }
            }
            $cron->last_run = now();
            $cron->next_run = now()->addSeconds($cron->schedule->interval);
            $cron->save();

            $cronLog->end_at = $cron->last_run;

            $startTime         = Carbon::parse($cronLog->start_at);
            $endTime           = Carbon::parse($cronLog->end_at);
            $diffInSeconds     = $startTime->diffInSeconds($endTime);
            $cronLog->duration = $diffInSeconds;
            $cronLog->save();
        }
        if (request()->target == 'all') {
            $notify[] = ['success', 'Cron executed successfully'];
            return back()->withNotify($notify);
        }
        if (request()->alias) {
            $notify[] = ['success', keyToTitle(request()->alias) . ' executed successfully'];
            return back()->withNotify($notify);
        }

    }

    public function fiatRate()
    {
        try {

            $general           = gs();
            $general->cron_run = [
                'fiat_cron'   => now(),
                'crypto_cron' => @$general->cron_run->crypto_cron,
                'kyc_cron'    => @$general->cron_run->kyc_cron,
            ];
            $general->save();

            $accessKey    = $general->fiat_currency_api;
            $baseCurrency = defaultCurrency();

            $curl = curl_init();

            $fiats = Currency::where('currency_type', 1)->pluck('currency_code')->toArray();
            $fiats = implode(',', $fiats);

            $url = "https://apilayer.net/api/live?access_key=$accessKey&currencies=$fiats&source=$baseCurrency&format=1";

            curl_setopt_array($curl, array(
                CURLOPT_URL            => $url,
                CURLOPT_HTTPHEADER     => array(
                    "Content-Type: text/plain",
                    "apikey: $accessKey",
                ),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING       => "",
                CURLOPT_MAXREDIRS      => 10,
                CURLOPT_TIMEOUT        => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST  => "GET",
            ));

            $response = curl_exec($curl);
            curl_close($curl);
            $response = json_decode($response);

            $rates = @$response->quotes;

            if (!@$rates) {
                echo @$response->message;
            }

            foreach (@$rates ?? [] as $currencyCode => $rate) {
                $currencyCode = str_replace($baseCurrency, '', $currencyCode);

                $currency = Currency::where('currency_code', $currencyCode)->first();

                $currency->rate = 1 / $rate;
                $currency->save();
            }

            echo "<br/>Executed...";
        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }
    }

    public function cryptoRate()
    {
        try {

            $general           = gs();
            $general->cron_run = [
                'fiat_cron'   => @$general->cron_run->fiat_cron,
                'crypto_cron' => now(),
            ];
            $general->save();

            $url     = 'https://pro-api.coinmarketcap.com/v1/cryptocurrency/quotes/latest';
            $cryptos = Currency::where('currency_type', Status::CRYPTO_CURRENCY)->pluck('currency_code')->toArray();
            $cryptos = implode(',', $cryptos);

            $parameters = [
                'symbol'  => $cryptos,
                'convert' => defaultCurrency(),
            ];

            $headers = [
                'Accepts: application/json',
                'X-CMC_PRO_API_KEY:' . trim($general->crypto_currency_api),
            ];

            $qs      = http_build_query($parameters); // query string encode the parameters
            $request = "{$url}?{$qs}"; // create the request URL
            $curl    = curl_init(); // Get cURL resource

            // Set cURL options
            curl_setopt_array($curl, array(
                CURLOPT_URL => $request, // set the request URL
                CURLOPT_HTTPHEADER => $headers, // set the headers
                CURLOPT_RETURNTRANSFER => 1, // ask for raw response instead of bool
            ));

            $response = curl_exec($curl); // Send the request, save the response
            curl_close($curl); // Close request
            
            $response = json_decode($response);

            if (!@$response->data) {
                echo 'error';
            }

            $coins = @$response->data ?? [];

            foreach (@$coins as $coin) {
                $currency = Currency::where('currency_code', $coin->symbol)->first();

                if ($currency) {
                    $defaultCurrency = defaultCurrency();
                    $currency->rate  = $coin->quote->$defaultCurrency->price;
                    $currency->save();
                }
            }

            echo "<br/>Executed...";

        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }
    }

}
