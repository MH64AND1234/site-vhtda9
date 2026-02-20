<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Upload extends Controller
{
    public function upload()
    {
        $file = $this->request->getFile('file');

        if ($file && !$file->hasMoved()) {

            $path = WRITEPATH . 'uploads/';
            if (!is_dir($path)) {
                mkdir($path, 0777, true);
            }

            $file->move($path, $file->getRandomName());
        }

        return redirect()->back();
    }

    public function delete($name)
    {
        $path = WRITEPATH . 'uploads/' . $name;

        if (file_exists($path)) {
            unlink($path);
        }

        return redirect()->back();
    }
}