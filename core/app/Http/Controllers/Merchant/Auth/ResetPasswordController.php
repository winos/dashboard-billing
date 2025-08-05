<?php

namespace App\Http\Controllers\Merchant\Auth;
use App\Models\Agent;
use Illuminate\Http\Request;
use App\Models\PasswordReset;
use App\Models\GeneralSetting;
use App\Models\AgentPasswordReset;
use App\Http\Controllers\Controller;
use App\Models\Merchant;
use App\Models\MerchantResetPassword;
use Illuminate\Support\Facades\Password;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Validation\Rules\Password as PassRule;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;
    
    
    public function broker()
    {
        return Password::broker('merchants');
    }

    public function showResetForm(Request $request, $token = null)
    {

        $email = session('fpass_email');
        $token = session()->has('token') ? session('token') : $token;
        if (MerchantResetPassword::where('token', $token)->where('email', $email)->count() != 1) {
            $notify[] = ['error', 'Invalid token'];
            return redirect()->route('merchant.password.request')->withNotify($notify);
        }
        return view(activeTemplate() . 'merchant.auth.passwords.reset')->with(
            ['token' => $token, 'email' => $email, 'pageTitle' => 'Reset Password']
        );
    }

    public function reset(Request $request)
    {

        session()->put('fpass_email', $request->email);
        $request->validate($this->rules(), $this->validationErrorMessages());
        $reset = MerchantResetPassword::where('token', $request->token)->orderBy('created_at', 'desc')->first();
        if (!$reset) {
            $notify[] = ['error', 'Invalid verification code'];
            return redirect()->route('merchant.login')->withNotify($notify);
        }

        $user = Merchant::where('email', $reset->email)->first();
        $user->password = bcrypt($request->password);
        $user->save();

        $userIpInfo = getIpInfo();
        $userBrowser = osBrowser();
        notify($user, 'PASS_RESET_DONE', [
            'operating_system' => @$userBrowser['os_platform'],
            'browser' => @$userBrowser['browser'],
            'ip' => @$userIpInfo['ip'],
            'time' => @$userIpInfo['time']
        ]);

        $notify[] = ['success', 'Password changed successfully'];
        return redirect()->route('merchant.login')->withNotify($notify);
    }



    /**
     * Get the password reset validation rules.
     *
     * @return array
     */
    protected function rules()
    {
        $general = gs();
        $passwordValidation = PassRule::min(6);
        if ($general->secure_password) {
            $passwordValidation = $passwordValidation->mixedCase()->numbers()->symbols()->uncompromised();
        }
        return [
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required','confirmed',$passwordValidation],
        ];
    }

}
