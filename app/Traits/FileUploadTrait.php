<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait FileUploadTrait
{
    public function uploadFile(Request $request, string $inputName, ?string $oldPath = null, string $path = '/uploads')
    {
        if ($request->hasFile($inputName)) {
            $file = $request->{$inputName};
            $extension = $file->getClientOriginalExtension();
            $fileName = 'media_' . uniqid() . '.' . $extension;

            $file->move(public_path($path), $fileName);

            return $path . '/' . $fileName;
        }
        return null;
    }
}
