<?php

namespace App\Http\Controllers\Panel;

// @todo добавить проверку на isImage

use App\Http\Controllers\Controller;
use Dukhanin\Panel\Traits\UploadsFiles;

class PanelTinymceUploadController extends Controller
{
    use UploadsFiles;

    public function upload()
    {
        upload()->authorize();

        if ($this->getUploadedFiles()->isEmpty()) {
            return $this->error()->response();
        }

        $this->uploadFileToDirectory($uploadedFile = $this->getUploadedFiles()->first());

        return response()->json(['location' => upload()->url($this->getDirectory() . '/' . $uploadedFile->getBasename())]);
    }
}
