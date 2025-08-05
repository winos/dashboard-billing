<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Lib\FormProcessor;
use Illuminate\Http\Request;

class KycController extends Controller
{
    public function setting($kycType = null)
    {
        $fixedType = $this->getKycType();

        if (!in_array($kycType, $fixedType)) {
            $kycType = 'user_kyc';
        }

        $pageTitle = $this->getKycTypeTitle($kycType) . ' Setting';
        $form = Form::where('act', $kycType)->first();
        return view('admin.kyc.setting', compact('pageTitle', 'form', 'kycType'));
    }

    public function settingUpdate(Request $request)
    {
        $formProcessor = new FormProcessor();
        $generatorValidation = $formProcessor->generatorValidation($this->getKycType(true));
        $request->validate($generatorValidation['rules'], $generatorValidation['messages']);

        $kycType = $request->kycType;
        $exist = Form::where('act', $kycType)->first();

        if ($exist) {
            $isUpdate = true;
        } else {
            $isUpdate = false;
        }

        $formProcessor->generate($kycType, $isUpdate, 'act');

        $notify[] = ['success', $this->getKycTypeTitle($kycType) . ' data updated successfully'];
        return back()->withNotify($notify);
    }

    protected function getKycType($implode = false)
    {
        $fixedType = ['user_kyc', 'agent_kyc', 'merchant_kyc'];

        if ($implode) {
            $fixedType = implode(',', $fixedType);
        }

        return $fixedType;
    }

    protected function getKycTypeTitle($kycType)
    {
        return ucfirst(explode('_', $kycType)[0]) . ' KYC';
    }
}
