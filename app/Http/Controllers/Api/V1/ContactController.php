<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactController extends BaseController
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|min:2|max:120',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:30',
            'subject' => 'required|string|min:3|max:180',
            'message' => 'required|string|min:10|max:5000',
            'website' => 'nullable|size:0',
        ]);

        unset($validated['website']);

        $message = ContactMessage::create(array_merge($validated, [
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
        ]));

        return $this->createdResponse([
            'id' => $message->id,
        ], 'تم استلام رسالتك بنجاح');
    }
}
