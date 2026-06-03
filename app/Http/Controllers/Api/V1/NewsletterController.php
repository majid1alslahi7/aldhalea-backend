<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Newsletter;
use Illuminate\Http\Request;

class NewsletterController extends BaseController
{
    public function subscribe(Request $request)
    {
        $request->validate(['email' => 'required|email|unique:newsletters,email']);
        Newsletter::create(['email' => $request->email, 'name' => $request->name]);
        return $this->successResponse(null, 'تم الاشتراك بنجاح');
    }

    public function unsubscribe(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $sub = Newsletter::where('email', $request->email)->first();
        if ($sub) $sub->unsubscribe();
        return $this->successResponse(null, 'تم إلغاء الاشتراك');
    }

    public function index() { return $this->successResponse(Newsletter::paginate(20)); }
    public function destroy($id) { Newsletter::find($id)?->delete(); return $this->deletedResponse(); }
    public function send(Request $request) { return $this->successResponse(null); }
}
