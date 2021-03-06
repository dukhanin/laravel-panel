<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Dukhanin\Panel\Files\File;
use Dukhanin\Panel\Traits\UploadsFiles;

class PanelFormUploadController extends Controller
{
    use UploadsFiles;

    public function upload()
    {
        upload()->authorize();

        if ($this->getUploadedFiles()->isEmpty()) {
            return response()->json([
                'error' => -1,
                'success' => false,
                'messages' => [],
                'data' => [],
            ]);
        }

        $files = $this->getUploadedFiles();

        $validExtensions = $this->getValidExtensions();

        if (!$this->validateFilesExt($files, $validExtensions))
            return response()->json([
                'error' => 1,
                'success' => false,
                'messages' => [__("files.extension_not_valid", ['values' => implode(', ', $validExtensions)])],
                'data' => $files,
            ]);

        foreach ($files as $file) {
            $this->uploadFileToDirectory($file);
            $file->save();
        }

        return response()->json([
            'error' => 0,
            'success' => true,
            'messages' => [],
            'data' => $files,
        ]);
    }

    public function createResize($id)
    {
        upload()->authorize();

        $options = request()->input();

        if (empty($file = File::find($id))) {
            return response()->json([
                'error' => -1,
                'success' => false,
                'messages' => ['file.not_found'],
                'data' => [],
            ]);
        }

        if (empty($resize = $file->getResize($options))) {
            return response()->json([
                'error' => -1,
                'success' => false,
                'messages' => ['file.error_creating_resize'],
                'data' => [],
            ]);
        }

        return response()->json([
            'error' => 0,
            'success' => true,
            'messages' => [],
            'data' => $resize,
        ]);
    }

    public function cropFromParent($id)
    {
        upload()->authorize();

        $settings = request()->input();

        if (empty($file = File::find($id)) || empty($original = $file->parent)) {
            return response()->json([
                'error' => -1,
                'success' => false,
                'messages' => ['file.not_found'],
                'data' => [],
            ]);
        }

        $copy = $original->copy($file->getPath());

        $file->setBaseFile($copy->getPath());

        $file->crop($settings['area']['w'], $settings['area']['h'], $settings['area']['x'], $settings['area']['y']);

        if (! empty($settings['size'])) {
            $file->resize($settings['size']);
        }

        $file->save();

        return response()->json([
            'error' => 0,
            'success' => true,
            'messages' => [],
            'data' => $file,
        ]);
    }

    public function delete($id)
    {
        upload()->authorize();

        if ($file = File::find($id)) {
            $file->delete();

            return response()->json([
                'error' => 0,
                'success' => true,
                'messages' => [],
                'data' => [],
            ]);
        }

        return response()->json([
            'error' => -1,
            'success' => false,
            'messages' => [],
            'data' => [],
        ]);
    }
}
