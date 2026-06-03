<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\MediaGallery;
use Illuminate\Http\Request;

class MediaController extends BaseController
{
    public function index()
    {
        return $this->successResponse(MediaGallery::latest()->paginate(20));
    }

    public function upload(Request $request)
    {
        $request->validate(['file' => 'required|file|max:10240']);
        $path = $request->file('file')->store('media', 'public');
        $media = MediaGallery::create([
            'file_path' => $path,
            'file_type' => $request->file('file')->getMimeType(),
            'user_id' => $request->user()->id,
        ]);
        return $this->createdResponse($media);
    }

    public function destroy($id)
    {
        MediaGallery::find($id)?->delete();
        return $this->deletedResponse();
    }

    public function uploadAvatar(Request $request)
    {
        $request->validate(['avatar' => 'required|image|max:2048']);
        $path = $request->file('avatar')->store('avatars', 'public');
        $request->user()->update(['avatar' => $path]);
        return $this->successResponse(['url' => asset('storage/' . $path)]);
    }
}
