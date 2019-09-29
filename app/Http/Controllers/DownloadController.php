<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

class DownloadController extends Controller
{
    public function __invoke($sid, $fname)
    {
        $fname = 'result.zip'; // override
        $disk = Storage::disk('local');
        $fpath = '/downloads/'.$sid.'.zip';
        if (!$disk->exists($fpath)) {
            return \App::abort(404);
        }

        return $disk->download($fpath, $fname);
    }
}
