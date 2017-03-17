<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Dukhanin\Panel\Files\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class PanelUploadController extends AbstractUploadController
{

    public function upload()
    {
        Gate::authorize('upload-files', Auth::user());

        if ($this->getUploadedFiles()->isEmpty()) {
            return response()->json([
                'error' => -1,
                'success' => false,
                'messages' => [],
                'data' => []
            ]);
        }

        $files = $this->getUploadedFiles();

        foreach ($files as $file) {
            $this->uploadFileToDirectory($file);

            $file->save();
        }

        return response()->json([
            'error' => 0,
            'success' => true,
            'messages' => [],
            'data' => $files
        ]);
    }


    public function createResize($id)
    {
        Gate::authorize('upload-files', Auth::user());

        $options = request()->input();

        if (empty($file = File::find($id))) {
            return response()->json([
                'error' => -1,
                'success' => false,
                'messages' => ['file.not_found'],
                'data' => []
            ]);
        }

        if (empty($resize = $file->getResize($options))) {
            return response()->json([
                'error' => -1,
                'success' => false,
                'messages' => ['file.error_creating_resize'],
                'data' => []
            ]);
        }

        return response()->json([
            'error' => 0,
            'success' => true,
            'messages' => [],
            'data' => $resize
        ]);
    }


    public function cropFromParent($id)
    {
        Gate::authorize('upload-files', Auth::user());

        $options = request()->input();

        if (empty($file = File::find($id)) || empty($original = $file->getParent())) {
            return response()->json([
                'error' => -1,
                'success' => false,
                'messages' => ['file.not_found'],
                'data' => []
            ]);
        }

        $copy = $original->copy($file->getPath());

        $file->setBaseFile($copy->getPath());

        $file->crop($options['area']['w'], $options['area']['h'], $options['area']['x'], $options['area']['y']);

        if (!empty($options['size'])) {
            $file->resize($options['size']);
        }

        $file->save();

        return response()->json([
            'error' => 0,
            'success' => true,
            'messages' => [],
            'data' => $file
        ]);
    }


    public function delete($id)
    {
        Gate::authorize('upload-files', Auth::user());

        if ($file = File::find($id)) {
            $file->delete();

            return response()->json([
                'error' => 0,
                'success' => true,
                'messages' => [],
                'data' => []
            ]);
        }

        return response()->json([
            'error' => -1,
            'success' => false,
            'messages' => [],
            'data' => []
        ]);
    }
}
