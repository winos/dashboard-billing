<?php

namespace App\Http\Controllers\Agent\Auth;
use Illuminate\Http\Request;
use App\Models\PasswordReset;
use App\Models\GeneralSetting;
use App\Models\AgentPasswordReset;
use App\Http\Controllers\Controller;
use App\Models\Agent;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PassRule;
use Illuminate\Foundation\Auth\ResetsPasswords;

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
        return Password::broker('agents');
    }

    public function showResetForm(Request $request, $token = null)
    {

        $email = session('fpass_email');
        $token = session()->has('token') ? session('token') : $token;
        if (AgentPasswordReset::where('token', $token)->where('email', $email)->count() != 1) {
            $notify[] = ['error', 'Invalid token'];
            return redirect()->route('agent.password.request')->withNotify($notify);
        }
        return view(activeTemplate() . 'agent.auth.passwords.reset')->with(
            ['token' => $token, 'email' => $email, 'pageTitle' => 'Reset Password']
        );
    }

    public function reset(Request $request)
    {
        session()->put('fpass_email', $request->email);
        $request->validate($this->rules(), $this->validationErrorMessages());
        $reset = AgentPasswordReset::where('token', $request->token)->orderBy('created_at', 'desc')->first();
        if (!$reset) {
            $notify[] = ['error', 'Invalid verification code'];
            return redirect()->route('agent.login')->withNotify($notify);
        }

        $user = Agent::where('email', $reset->email)->first();
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
        return redirect()->route('agent.login')->withNotify($notify);
    }



    /**
     * Get the password reset validation rules.
     *
     * @return array
     */
    protected function rules()
    {
        $passwordValidation = PassRule::min(6);
        $general = gs();
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
