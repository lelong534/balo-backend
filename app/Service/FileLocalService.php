<?php

namespace App\Service;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileLocalService implements IFileService
{
    public function saveFile(UploadedFile $file)
    {
        $path = $file->store("public/files");
        return $path;
    }
    public function deleteFile(string $link)
    {
        Storage::delete($link);
    }
}
