<?php

namespace App\Http\Controllers\Ajax;

use Response;
use Storage;
use App\Traits\FileProcesser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\UploadImageRequest;

class UploadImageController extends Controller
{
    use FileProcesser;

    public function insertImage(UploadImageRequest $request)
    {
        if ($request->ajax()) {
            $filePath = $this->uploadImage($request, 'image', config('settings.path_upload_image'));
            $urlImage = Storage::url($filePath);

            return response()->json($urlImage);
        }

        return response()->json(false);
    }
}