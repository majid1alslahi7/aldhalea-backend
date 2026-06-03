<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
        $settings = $request->input('settings');
        if (!is_array($settings)) {
            $settings = $request->except(['_method']);
        }

        $validator = Validator::make(['settings' => $settings], [
            'settings' => 'required|array',
        ]);

        $validator->after(function ($validator) use ($settings) {
            foreach ($settings as $key => $value) {
                if (!is_string($key) || !preg_match('/^[a-zA-Z0-9_.-]{2,100}$/', $key)) {
                    $validator->errors()->add('settings', 'مفتاح إعداد غير صالح');
                }

                if (!is_null($value) && !is_scalar($value) && !is_array($value)) {
                    $validator->errors()->add($key, 'قيمة إعداد غير صالحة');
                }
            }
        });

        $validator->validate();

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['key' => $key], [
                'value' => is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value,
            ]);
        }

        return $this->successResponse(null, 'تم تحديث الإعدادات');
    }
}
