<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends BaseController
{
    public function index(Request $request)
    {
        return $this->successResponse(
            Notification::where('user_id', $request->user()->id)->latest()->paginate(20)
        );
    }

    public function unreadCount(Request $request)
    {
        return $this->successResponse([
            'count' => Notification::where('user_id', $request->user()->id)->unread()->count()
        ]);
    }

    public function markAsRead($id) { Notification::find($id)?->markAsRead(); return $this->successResponse(null); }
    public function markAllAsRead(Request $request) { Notification::where('user_id', $request->user()->id)->unread()->update(['is_read' => true, 'read_at' => now()]); return $this->successResponse(null); }
    public function destroy($id) { Notification::find($id)?->delete(); return $this->deletedResponse(); }
}
