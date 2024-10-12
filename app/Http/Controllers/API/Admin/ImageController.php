<?php

namespace App\Http\Controllers\API\Admin;

use App\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ImageService;

class ImageController extends Controller
{

    public function singleUpload(Request $request)
    {
        try {

            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,webp,jpg,gif,svg|max:5120', //5mb
            ]);

            $file = $request->file('image');

            if (!$file->isValid()) {
                return ApiResponse::error(null, 'Invalid file upload', 400);
            }

            $image_result = ImageService::uploadSingleImageToS3($file, 'car');

            if ($image_result['status'] == true) {
                return ApiResponse::success($image_result['data'], 'Image upload successful', 201);
            } else {
                return ApiResponse::error(null, 'Error when uploading image ' . $image_result['message'], 500);
            }
        } catch (\Exception $e) {
            return ApiResponse::error(null, 'Error when uploading image ' . $e->getMessage(), 500);
        }
    }

    public function delete($id)
    {
        try {
            $result = ImageService::deleteImageRecord($id, null);  //common fun require two params

            if ($result['status']) {
                return ApiResponse::success(null, 'Image deleted successfully.', 200);
            } else {
                return ApiResponse::error(null, 'Error when deleting image ' . $result['message'], 500);
            }
        } catch (\Exception $e) {
            return ApiResponse::error(null, 'Error when deleting image ' . $e->getMessage(), 500);
        }
    }

    public function deleteImageFromS3(Request $request)
    {
        $imageUrls = (object) $request->all();

        if ($imageUrls && !empty((array) $imageUrls)) {
            try {
                $result = ImageService::deleteImageRecord(null, $imageUrls);

                if ($result['status']) {
                    return ApiResponse::success(null, 'Image deleted successfully.', 200);
                } else {
                    return ApiResponse::error(null, 'Error when deleting image ' . $result['message'], 500);
                }
            } catch (\Exception $e) {
                return ApiResponse::error(null, 'Error when deleting image ' . $e->getMessage(), 500);
            }
        }

        return ApiResponse::error(null, 'There is no image urls in your passed data', 500);
    }
}
