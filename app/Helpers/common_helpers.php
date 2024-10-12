<?php
use Illuminate\Support\Str;
use App\Models\Car;
use App\Models\Manufacturer;
use App\Models\CarModel;
use App\Models\OTP;
use Illuminate\Support\Carbon;
use Intervention\Image\Facades\Image AS ImageFacade;

function test()
{
    return 'hello';
}

function generateCarShortId($length = 10)
{
    return strtoupper(Str::random($length));
}

function generateCarUniqueSlug($car)
{
    $manufacturerName = 'unknown';
    if (isset($car['manufacturer_id'])) {
        $manufacturer = Manufacturer::find($car['manufacturer_id']);
        $manufacturerName = $manufacturer ? $manufacturer->name : 'unknown';
    }

    $modelName = 'unknown';
    if (isset($car['model_id'])) {
        $carModel = CarModel::find($car['model_id']);
        $modelName = $carModel ? $carModel->name : 'unknown';
    }

    $slugBase = sprintf('%s-%s-%s-%s', $car['production_year'], Str::slug($manufacturerName), Str::slug($modelName), $car['short_id']);
    $slug = $slugBase;
    $count = 1;

    while (Car::where('slug', $slug)->exists()) {
        $slug = $slugBase . '-' . $count++;
    }

    return $slug;
}

function OtpValidate($request)
{
    $status = null;

    $otpEntry = OTP::where('email', $request['email'])
               ->where('expired_at', '>', Carbon::now())
               ->orderBy('created_at', 'desc')
               ->first();

    if ($otpEntry && ($request['otp'] == $otpEntry->otp)) 
    {
        OTP::where('email', $request['email'])
            ->update([
                'is_used' => 1
            ]);
        $status = true;

    } else {
        $status = false;
    }

    return $status;
}

function paginateData($query, $page, $perPage, $totalItems)
{
    $offset = ($page - 1) * $perPage;
    $totalPages = ceil($totalItems / $perPage);
    
    $meta = [
        'current_page' => $page,
        'per_page' => $perPage,
        // 'total_items' => $totalItems,
        'total_pages' => $totalPages,
    ];

    $models = $query->skip($offset)
                    ->take($perPage)
                    ->get();
    return  [
                "data" => $models,
                "meta" => $meta
            ];
}

function checkPermitImageSize($file) {
    $image = ImageFacade::make($file->getRealPath());
    $width = $image->width();
    $height = $image->height();
    $status = false;

    if($width < 928 || $height < 557 )
    {
        $status = true;
    }
    return $status;
}

function generateSlug($model, $name){
   $slug = sprintf(Str::slug($name));
   $slugBase = $slug;
   $count = 1;
    
    while ($model::where('slug', $slug)->exists()) { 
        $slug = $slugBase . '-' . $count++;
    }
    return $slug;
}