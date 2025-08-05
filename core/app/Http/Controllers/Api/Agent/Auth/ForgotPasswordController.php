<?php

namespace App\Http\Controllers\Api\Agent\Auth;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\AgentPasswordReset;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Validator;

class ForgotPasswordController extends Controller
{
    public function sendResetCodeEmail(Request $request)
    {   
        $validator = Validator::make($request->all(), [
            'value'=>'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'remark'=>'validation_error',
                'status'=>'error',
                'message'=>['error'=>$validator->errors()->all()],
            ]);
        }

        $fieldType = $this->findFieldType();
        $user = Agent::where($fieldType, $request->value)->first();

        if (!$user) {
            $notify[] = 'Couldn\'t find any account with this information';
            return response()->json([
                'remark'=>'validation_error',
                'status'=>'error',
                'message'=>['error'=>$notify],
            ]);
        }

        AgentPasswordReset::where('email', $user->email)->delete();
        $code = verificationCode(6);
        $password = new AgentPasswordReset();
        $password->email = $user->email;
        $password->token = $code;
        $password->created_at = \Carbon\Carbon::now();
        $password->save();

        $userIpInfo = getIpInfo();
        $userBrowserInfo = osBrowser();
        notify($user, 'PASS_RESET_CODE', [
            'code' => $code,
            'operating_system' => @$userBrowserInfo['os_platform'],
            'browser' => @$userBrowserInfo['browser'],
            'ip' => @$userIpInfo['ip'],
            'time' => @$userIpInfo['time']
        ],['email']);

        $email = $user->email;
        $response[] = 'Verification code sent to mail';
        return response()->json([
            'remark'=>'code_sent',
            'status'=>'success',
            'message'=>['success'=>$response],
            'data'=>[
                'email'=>$email
            ]
        ]);
    }

    public function verifyCode(Request $request)
    {   
        $validator = Validator::make($request->all(), [
            'code' => 'required',
            'email' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'remark'=>'validation_error',
                'status'=>'error',
                'message'=>['error'=>$validator->errors()->all()],
            ]);
        }
        $code =  $request->code;

        if (AgentPasswordReset::where('token', $code)->where('email', $request->email)->count() != 1) {
            $notify[] = 'Verification code doesn\'t match';
            return response()->json([
                'remark'=>'validation_error',
                'status'=>'error',
                'message'=>['error'=>$notify],
            ]);
        }

        $response[] = 'You can change your password.';
        return response()->json([
            'remark'=>'verified',
            'status'=>'success',
            'message'=>['success'=>$response],
        ]);
    }

    public function reset(Request $request)
    {  

        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return response()->json([
                'status'=>'error',
                'message'=>['error'=>$validator->errors()->all()],
            ]);
        }


        $reset = AgentPasswordReset::where('token', $request->token)->orderBy('created_at', 'desc')->first();
        if (!$reset) {
            $response[] = 'Invalid verification code.';
            return response()->json([
                'remark'=>'validation_error',
                'status'=>'error',
                'message'=>['success'=>$response],
            ]);
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
        ],['email']);


        $response[] = 'Password changed successfully';
        return response()->json([
            'remark'=>'password_changed',
            'status'=>'success',
            'message'=>['success'=>$response],
        ]);
    }

    protected function rules()
    {
        $passwordValidation = Password::min(6);
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

    private function findFieldType()
    {
        $input = request()->input('value');

        $fieldType = filter_var($input, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        request()->merge([$fieldType => $input]);
        return $fieldType;
    }
}
