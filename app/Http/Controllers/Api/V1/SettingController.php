<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends BaseController
{
    public function public()
    {
        $settings = Setting::public()->get()->pluck('value', 'key');
        return $this->successResponse($settings);
    }

    public function all()
    {
        return $this->successResponse(Setting::all()->pluck('value', 'key'));
    }

    public function update(Request $request)
    {
        foreach ($request->all() as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        return $this->successResponse(null, 'تم تحديث الإعدادات');
    }
}
