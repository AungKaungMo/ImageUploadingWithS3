<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use App\Models\Image;
use Intervention\Image\Facades\Image As ImageFacade;
use Illuminate\Http\UploadedFile AS File;


class ImageService
{
    public static function uploadSingleImageToS3($imageFile, $model)
    {
        try {
            $url_sizes = [
                'thumb_url' => [],
                'small_url' => [150, 112], 
                'medium_url' => [300, 225], 
                // 'large_url' => [500, 375], 
                'large_url' => [635, 357.19], 
            ];
    
            $url_data = [];

            foreach ($url_sizes as $url_key => $url_size) 
            {
                if($url_key != 'thumb_url')
                {
                    $file = self::imageCompress($imageFile, $url_size); //get compressed UploadedFile instance
                    $filePath = $file->store($model, 's3');
                } else {
                    $filePath = $imageFile->store($model, 's3');
                }
               
                $url = Storage::disk('s3')->url($filePath);
                $url_data[$url_key] = $url;

                if($url_key != 'thumb_url') {
                    $tempImagePath = $file->path();
                    if (file_exists($tempImagePath)) {
                        unlink($tempImagePath);
                    }
                }
            }

            $image = [
                'thumb_url' => $url_data['thumb_url'],
                'small_url' => $url_data['small_url'],
                'medium_url' => $url_data['medium_url'],
                'large_url' => $url_data['large_url'],
                'file_name' => $imageFile->getClientOriginalName(),
                'file_size' => $imageFile->getSize(),
                'file_type' => $imageFile->getClientMimeType()
            ];

            return [
                'data' => $image,
                'status' => true,
            ];

        } catch (\Exception $e) {
            return [
                'message' => $e->getMessage(),
                'status' => false,
            ];
        }
    }

    public static function saveImages(array $images, int $imageableId, string $imageableType): void
    {
        foreach ($images as $imageData) {
            $image = new Image();
            $image->imageable_id = $imageableId;
            $image->imageable_type = $imageableType;
            $image->fill($imageData);
            $image->save();
        }
    }

    public static function deleteImageFromS3($url)
    {
        try {
            $filePath = parse_url($url, PHP_URL_PATH);
            $filePath = ltrim($filePath, '/');

            if (Storage::disk('s3')->exists($filePath)) {
                Storage::disk('s3')->delete($filePath);
            }

            return [
                'status' => true,
            ];
        } catch (\Exception $e) {
            return [
                'message' => $e->getMessage(),
                'status' => false,
            ];
        }
    }

    public static function deleteImageRecord($id=null, $image=null)
    {
        if($image == null)
        {
            $image = Image::findOrFail($id);
        }

        try {
            
            for ($i=0; $i <4 ; $i++) { 
                switch($i) {
                    case 0: self::deleteImageFromS3($image->large_url); 
                            break;

                    case 1: self::deleteImageFromS3($image->medium_url); 
                            break;

                    case 2: self::deleteImageFromS3($image->small_url); 
                            break;

                    case 3: self::deleteImageFromS3($image->thumb_url); 
                            break;
                }
            }

            if($id != null)
            {
                $image->delete();
            }

            return [
                'status' => true,
            ];

        } catch (\Exception $e) {
            return [
                'message' => $e->getMessage(),
                'status' => false,
            ];
        }
    }

    private static function imageCompress($file, $url_size)
    {
        // Process the image
        $image = ImageFacade::make($file->getRealPath());
            
        // Resize and compress the image
            // $image->resize($url_size[0], $url_size[1], function ($constraint) {
            //     // $constraint->aspectRatio();
            //     $constraint->upsize();   //do not define aspect ratio
            // })->encode('jpg', 75);

        $image->resize($url_size[0], $url_size[1])->encode('jpg', 100);

        // Add watermark
        // $watermark = ImageFacade::make(public_path('watermark.png'));
        // $watermarkSize = $image->width() / 8; // 25% of the image width
        // $watermark->resize($watermarkSize, null, function ($constraint) {
        //     $constraint->aspectRatio();
        // });

        // $image->insert($watermark, 'top-left', 10, 10);

        $tempImagePath = storage_path('app/temp');
        if (!is_dir($tempImagePath)) {
            mkdir($tempImagePath, 0775, true); 
        }

        // Generate a unique filename and path
        $tempImagePath = storage_path('app/temp/' . time() . '.' . $file->getClientOriginalExtension());
        $image->save($tempImagePath);

        // Convert the temporary file to an UploadedFile instance
        $uploadedFile = new File(
            $tempImagePath,
            $file->getClientOriginalName(),
            $file->getClientMimeType(),
            null,
            true
        );
        
        return $uploadedFile;
    }
}
