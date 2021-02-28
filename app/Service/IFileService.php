<?php

namespace App\Service;

use Illuminate\Http\UploadedFile;

interface IFileService
{
    public function saveFile(UploadedFile $file);
    public function deleteFile(string $link);
}
