<?php

namespace Dukhanin\Panel\Http\Controllers;

// @todo добавить проверку на isImage

class PanelTinymceUploadController extends AbstractUploadController
{

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
