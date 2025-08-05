<?php

namespace App\Http\Controllers\Api\Agent\Auth;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\Agent;
use App\Models\Currency;
use App\Models\UserLogin;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;


    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {   
        $general = gs();
        $passwordValidation = Password::min(6);
        if ($general->secure_password) {
            $passwordValidation = $passwordValidation->mixedCase()->numbers()->symbols()->uncompromised();
        }
        $agree = 'nullable';
        if ($general->agree) {
            $agree = 'required';
        }
        $countryData = (array)json_decode(file_get_contents(resource_path('views/partials/country.json')));
        $countryCodes = implode(',', array_keys($countryData));
        $mobileCodes = implode(',',array_column($countryData, 'dial_code'));
        $countries = implode(',',array_column($countryData, 'country'));
        $validate = Validator::make($data, [
            'email' => 'required|string|email|unique:agents',
            'mobile' => 'required|integer',
            'password' => ['required','confirmed',$passwordValidation],
            'username' => 'required|alpha_num|unique:agents|min:6',
            'captcha' => 'sometimes|required',
            'mobile_code' => 'required|in:'.$mobileCodes,
            'country_code' => 'required|in:'.$countryCodes,
            'country' => 'required|in:'.$countries,
            'agree' => $agree
        ]);
        return $validate;
    }


    public function register(Request $request)
    {   
        if (!gs('registration')) {
            $notify[] = 'Registration not allowed';
            return response()->json([
                'remark' => 'validation_error',
                'status' => 'error',
                'message' => ['error' => $notify],
            ]);
        }
        
        $validator = $this->validator($request->all());
        if ($validator->fails()) {
            return response()->json([
                'remark'=>'validation_error',
                'status'=>'error',
                'message'=>['error'=>$validator->errors()->all()],
            ]);
        }

        if(preg_match("/[^a-z0-9_]/", trim($request->username))){
            $response[] = 'No special character, space or capital letters in username.';
            return response()->json([
                'remark'=>'validation_error',
                'status'=>'error',
                'message'=>['error'=>$response],
            ]);
        }

        $exist = Agent::where('mobile',$request->mobile_code.$request->mobile)->first();
        if ($exist) {
            $response[] = 'The mobile number already exists';
            return response()->json([
                'remark'=>'validation_error',
                'status'=>'error',
                'message'=>['error'=>$response],
            ]);
        }

        $user = $this->create($request->all());
        $this->registered($request, $user);

        $response['access_token'] =  $user->createToken('auth_token', ['agent'])->plainTextToken;
        $response['agent'] = $user;
        $response['token_type'] = 'Bearer';
        $notify[] = 'Registration successfully';
        return response()->json([
            'remark'=>'registration_success',
            'status'=>'success',
            'message'=>['success'=>$notify],
            'data'=>$response
        ]);

    }


    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array $data
     * @return \App\Agent
     */
    protected function create(array $data)
    {   

        $general = gs();

        $referBy = session()->get('reference');
        if ($referBy) {
            $referUser = Agent::where('username', $referBy)->first();
        } else {
            $referUser = null;
        }
        //User Create
        $agent = new Agent();
        $agent->firstname = isset($data['firstname']) ? $data['firstname'] : null;
        $agent->lastname = isset($data['lastname']) ? $data['lastname'] : null;
        $agent->email = strtolower($data['email']);
        $agent->password = Hash::make($data['password']);
        $agent->username = $data['username'];
        $agent->ref_by = $referUser ? $referUser->id : 0;
        $agent->country_code = $data['country_code'];
        $agent->mobile = $data['mobile_code'].$data['mobile'];

       $agent->address = [
            'address' => '',
            'state' => '',
            'zip' => '',
            'country' => isset($data['country']) ? $data['country'] : null,
            'city' => ''
        ];

        $agent->status = 1;
        $agent->kv = $general->kv ? 0 : 1;
        $agent->ev = $general->ev ? 0 : 1;
        $agent->sv = $general->sv ? 0 : 1;
        $agent->ts = 0;
        $agent->tv = 1;
        $agent->save();

        $adminNotification = new AdminNotification();
        $adminNotification->user_type = 'AGENT';
        $adminNotification->user_id = $agent->id;
        $adminNotification->title = 'New agent registered';
        $adminNotification->click_url = urlPath('admin.agents.detail',$agent->id);
        $adminNotification->save();

        //Login Log Create
        $ip = $_SERVER["REMOTE_ADDR"];
        $exist = UserLogin::where('user_ip',$ip)->first();
        $userLogin = new UserLogin();

        //Check exist or not
        if ($exist) {
            $userLogin->longitude =  $exist->longitude;
            $userLogin->latitude =  $exist->latitude;
            $userLogin->city =  $exist->city;
            $userLogin->country_code = $exist->country_code;
            $userLogin->country =  $exist->country;
        }else{
            $info = json_decode(json_encode(getIpInfo()), true);
            $userLogin->longitude =  @implode(',',$info['long']);
            $userLogin->latitude =  @implode(',',$info['lat']);
            $userLogin->city =  @implode(',',$info['city']);
            $userLogin->country_code = @implode(',',$info['code']);
            $userLogin->country =  @implode(',', $info['country']);
        }

        $userAgent = osBrowser();
        $userLogin->agent_id = $agent->id;
        $userLogin->user_ip =  $ip;
        
        $userLogin->browser = @$userAgent['browser'];
        $userLogin->os = @$userAgent['os_platform'];
        $userLogin->save();

        return $agent;
    }

    public function registered(Request $request, $agent)
    {  
        foreach(Currency::enable()->get(['id', 'currency_code']) as $currency){
            $exist = $agent->wallets()->where('currency_id', $currency->id)->exists();
            if(!$exist){
                createWallet($currency,$agent);
            }
        }
    }

}
