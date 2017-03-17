<?php

namespace App\Http\Controllers\Panel;

// @todo добавить проверку на isImage

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class PanelTinymceUploadController extends AbstractUploadController
{

    public function upload()
    {
        Gate::authorize('upload-files', Auth::user());

        if ($this->getUploadedFiles()->isEmpty()) {
            return $this->error()->response();
        }

        $this->uploadFileToDirectory($uploadedFile = $this->getUploadedFiles()->first());

        return response()->json([ 'location' => upload()->url($this->getDirectory() . '/' . $uploadedFile->getBasename()) ]);
    }
}
