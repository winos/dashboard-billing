<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ModuleSetting;
use Illuminate\Http\Request;

class ModuleSettingController extends Controller
{
    
    public function index()
    {   
        $pageTitle = "System Modules Settings";
        $modules = ModuleSetting::get();
        return view('admin.setting.module_setting',compact('pageTitle','modules'));
    }

    public function update(Request $request)
    {    
        $module = ModuleSetting::findOrFail($request->id);
        $name = ucwords(str_replace('_',' ',$module->slug));
        $userType = ucfirst(strtolower($module->user_type));
        
        if($module->status == 1) {
            $module->status = 0;
            $msg = $userType .' '.$name.' is turned off';
        } else {
            $module->status = 1;
            $msg = $userType.' '.$name.' is turned on';
        }
        
        $module->save();
        return ['success'=> $msg];
    }

}
