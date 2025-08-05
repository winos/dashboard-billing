<?php

namespace App\Http\Controllers\Api\Merchant\Auth;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\Currency;
use App\Models\Merchant;
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
            'email' => 'required|string|email|unique:merchants',
            'mobile' => 'required|integer',
            'password' => ['required','confirmed',$passwordValidation],
            'username' => 'required|alpha_num|unique:merchants|min:6',
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

        $exist = Merchant::where('mobile',$request->mobile_code.$request->mobile)->first();
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

        $response['access_token'] =  $user->createToken('auth_token', ['merchant'])->plainTextToken;
        $response['merchant'] = $user;
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
     * @return \App\Merchant
     */
    protected function create(array $data)
    {   

        $general = gs();

        $referBy = session()->get('reference');
        if ($referBy) {
            $referUser = Merchant::where('username', $referBy)->first();
        } else {
            $referUser = null;
        }
        //User Create
        $merchant = new Merchant();
        $merchant->firstname = isset($data['firstname']) ? $data['firstname'] : null;
        $merchant->lastname = isset($data['lastname']) ? $data['lastname'] : null;
        $merchant->email = strtolower($data['email']);
        $merchant->password = Hash::make($data['password']);
        $merchant->username = $data['username'];
        $merchant->ref_by = $referUser ? $referUser->id : 0;
        $merchant->country_code = $data['country_code'];
        $merchant->mobile = $data['mobile_code'].$data['mobile'];

       $merchant->address = [
            'address' => '',
            'state' => '',
            'zip' => '',
            'country' => isset($data['country']) ? $data['country'] : null,
            'city' => ''
        ];

        $merchant->status = 1;
        $merchant->kv = $general->kv ? 0 : 1;
        $merchant->ev = $general->ev ? 0 : 1;
        $merchant->sv = $general->sv ? 0 : 1;
        $merchant->ts = 0;
        $merchant->tv = 1;
        $merchant->save(); 

        $adminNotification = new AdminNotification();
        $adminNotification->user_type = 'MERCHANT';
        $adminNotification->user_id = $merchant->id;
        $adminNotification->title = 'New merchant registered';
        $adminNotification->click_url = urlPath('admin.merchants.detail',$merchant->id);
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

        $userMerchant = osBrowser();
        $userLogin->merchant_id = $merchant->id;
        $userLogin->user_ip =  $ip;
        
        $userLogin->browser = @$userMerchant['browser'];
        $userLogin->os = @$userMerchant['os_platform'];
        $userLogin->save();

        return $merchant;
    }

    public function registered(Request $request, $merchant)
    {  
        foreach(Currency::enable()->get(['id', 'currency_code']) as $currency){
            $exist = $merchant->wallets()->where('currency_id', $currency->id)->exists();
            if(!$exist){
                createWallet($currency,$merchant);
            }
        }
    }

}
