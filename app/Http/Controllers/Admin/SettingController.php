<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function registerSetting(Request $request)
    {
        $setting = Setting::where('param_name', 'register')->first();
        if(!$setting){
            return redirect()->back()->withError('Setting not found');
        }
        return view('admin.settings.index',['setting'=>$setting]);
    }

    public function update(Request $request)
    {
        if($request->ajax()) {

            $setting = Setting::where('id', $request->id)->where('param_name', $request->param_name)->first();
            if(!$setting){
                $result = ['status' => false, 'message' => 'Parameter not found', 'data' => []];
                return response()->json($result, 404);
            }

            $message = $request->status ? "on" : "off";
            $setting->param_value = $request->status;
            if($setting->save()){
                $result = ['status' => true, 'message' => 'User register '.$message.' successfully.'];
            }else{
                $result = ['status' => false, 'message' => 'Setting update fail!'];
            }

            return response()->json($result);
        }
    }
}
