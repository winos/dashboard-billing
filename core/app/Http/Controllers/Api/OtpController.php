<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Common;
use App\Http\Controllers\Controller;
use App\Models\UserAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OtpController extends Controller
{

    use Common;

    public function otpResend(Request $request)
    {

        $general    = gs();
        $userAction = $this->getAction($request->action_id);

        if (!$userAction) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => ['Something went wrong']],
            ]);
        }

        $userAction->otp        = verificationCode(6);
        $userAction->send_at    = now();
        $userAction->expired_at = now()->addSeconds((float) $general->otp_expiration);
        $userAction->save();

        if ($userAction->type == '2fa') {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => ['Cannot sent 2FA verification']],
            ]);
        }

        notify(
            $this->guard()['user'],
            'OTP',
            ['code' => $userAction->otp],
            [$userAction->type]
        );

        return response()->json([
            'remark'  => 'resend_otp',
            'status'  => 'success',
            'message' => ['success' => ['Resend otp']],
        ]);
    }

    public function otpVerify(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'code'      => 'required',
            'action_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => $validator->errors()->all()],
            ]);
        }

        $code       = str_replace(' ', '', $request->code);
        $userAction = $this->getAction($request->action_id);
        $guard      = strtolower($this->guard()['user_type']);

        if (!$userAction) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => ['Sorry! Unable to process']],
            ]);
        }

        if ($userAction->type == 'email' || $userAction->type == 'sms') {
            if ($userAction->otp != $code) {
                return response()->json([
                    'remark'  => 'validation_error',
                    'status'  => 'error',
                    'message' => ['error' => ['OTP doesn\'t match']],
                ]);
            }
            if ($userAction->expired_at < now()) {
                return response()->json([
                    'remark'  => 'validation_error',
                    'status'  => 'error',
                    'message' => ['error' => ['Your OTP has expired']],
                ]);
            }
        } else {
            $user     = $userAction->$guard;
            $response = verifyG2fa($user, $code);
            if (!$response) {
                return response()->json([
                    'remark'  => 'validation_error',
                    'status'  => 'error',
                    'message' => ['error' => ['Verification code doesn\'t match']],
                ]);
            }
        }

        $userAction->used_at = now();
        $userAction->save();

        return callApiMethod($userAction->details->done_route, $userAction->id);
    }

    protected function getAction($actionId)
    {
        $user     = $this->guard()['user'];
        $userType = $this->guard()['user_type'];

        return UserAction::where('user_id', $user->id)->where('is_otp', 1)->where('is_api', 1)->where('used', 0)->where('user_type', $userType)
            ->where('id', $actionId)->first();
    }

}
