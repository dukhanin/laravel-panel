<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Dukhanin\Panel\Files\File;

class PanelUploadController extends Controller
{

    protected $directory;


    public function upload()
    {
        if ($this->getUploadedFiles()->isEmpty()) {
            return response()->json([
                'error'    => -1,
                'success'  => false,
                'messages' => [],
                'data'     => []
            ]);
        }

        $files = $this->getUploadedFiles();

        foreach ($files as $file) {
            $this->uploadFileToDirectory($file);

            $file->save();
        }

        return response()->json([
            'error'    => 0,
            'success'  => true,
            'messages' => [],
            'data'     => $files
        ]);
    }


    public function createResize($id)
    {
        $options = request()->input();

        if (empty($file = File::find($id))) {
            return response()->json([
                'error'    => -1,
                'success'  => false,
                'messages' => [ 'file.not_found' ],
                'data'     => []
            ]);
        }

        if (empty($resize = $file->getResize($options))) {
            return response()->json([
                'error'    => -1,
                'success'  => false,
                'messages' => [ 'file.error_creating_resize' ],
                'data'     => []
            ]);
        }

        return response()->json([
            'error'    => 0,
            'success'  => true,
            'messages' => [],
            'data'     => $resize
        ]);
    }


    public function cropFromParent($id)
    {
        $options = request()->input();

        if (empty($file = File::find($id)) || empty($original = $file->getParent())) {
            return response()->json([
                'error'    => -1,
                'success'  => false,
                'messages' => [ 'file.not_found' ],
                'data'     => []
            ]);
        }

        $copy = $original->copy($file->getPath());

        $file->setBaseFile($copy->getPath());

        $file->crop($options['area']['w'], $options['area']['h'], $options['area']['x'], $options['area']['y']);

        if ( ! empty($options['size'])) {
            $file->resize($options['size']);
        }

        $file->save();

        return response()->json([
            'error'    => 0,
            'success'  => true,
            'messages' => [],
            'data'     => $file
        ]);
    }


    public function delete($id)
    {
        if ($file = File::find($id)) {
            $file->delete();

            return response()->json([
                'error'    => 0,
                'success'  => true,
                'messages' => [],
                'data'     => []
            ]);
        }

        return response()->json([
            'error'    => -1,
            'success'  => false,
            'messages' => [],
            'data'     => []
        ]);
    }


    protected function getUploadedFiles()
    {
        return collect(request()->file('file'))->map(function ($uploadedFile) {
            $file = new File();
            $file->setBaseFile($uploadedFile);

            return $file;
        });
    }


    protected function uploadFileToDirectory($file)
    {
        $uploadPath = upload()->path($this->getDirectory());

        $fileBasename = $file->getClientOriginalName();

        $this->renameIfExists($uploadPath, $fileBasename);

        $file->move($uploadPath . '/' . $fileBasename);

        chmod($file->getPath(), 0644); // @todo do smth with

        return true;
    }


    protected function renameIfExists($uploadPath, &$fileBasename)
    {
        $fileName = pathinfo($fileBasename, PATHINFO_FILENAME);
        $fileExt  = pathinfo($fileBasename, PATHINFO_EXTENSION);
        $counter  = 0;

        while (file_exists($uploadPath . '/' . $fileBasename)) {
            $fileBasename = $fileName . '(' . ( ++$counter ) . ')';

            if ($fileExt) {
                $fileBasename .= '.' . $fileExt;
            }
        }
    }


    protected function initDirectory()
    {
        $this->directory = request()->input('directory');

        upload()->makeDirectoryIfNotExists($this->directory);
    }


    protected function getDirectory()
    {
        if (is_null($this->directory)) {
            $this->initDirectory();
        }

        return $this->directory;
    }
}
