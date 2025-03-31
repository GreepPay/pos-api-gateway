<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Str;

trait FileUploadTrait
{
    /**
     * Uploads a file from the request to Azure Blob Storage.
     *
     * Expects the file to be available under the key 'attachment'.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  bool  $resizeImg
     * @return string The URL of the uploaded file.
     *
     * @throws \InvalidArgumentException if no file is found under 'attachment'
     */
    public function uploadFile(Request $request, $resizeImg = true)
    {
        if (!$request->hasFile('attachment')) {
            throw new \InvalidArgumentException("No file found under 'attachment'");
        }

        $file = $request->file('attachment');
        $allowedMimeTypes = ['image/jpeg', 'image/gif', 'image/png'];
        $contentType = $file->getClientMimeType();

        $photo = null;
        if (!in_array($contentType, $allowedMimeTypes)) {
            // For non-image files, simply upload them as-is.
            $photo = Storage::disk('azure')->putFile('main', $file, 'public');
        } else {
            // For images, process and encode them
            $data = getimagesize($file);
            $width = $data[0];
            $height = $data[1];

            // Process image: encode as jpeg at 60% quality
            $image = Image::make($file)->encode('jpeg', 60);
            if ($resizeImg === false) {
                $image = Image::make($file)->encode('jpeg', 60);
            }

            $extension = $file->getClientOriginalExtension() ?: 'jpg';
            $fileName = Str::random(30) . '.' . $extension;
            $imageStream = $image->stream();

            // Upload the processed image to Azure
            $photo = Storage::disk('azure')->put('main/' . $fileName, $imageStream->__toString(), 'public');

            return env('AZURE_STORAGE_URL', "https://shpt.blob.core.windows.net") . '/main/' . $fileName;
        }

        return Storage::disk('azure')->url($photo);
    }
}
