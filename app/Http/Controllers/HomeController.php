<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use SimPF\Utility\SshConnection;

class HomeController extends Controller
{
    public function __invoke(Request $request)
    {
        $params = [
            'url' => $request->input('url', ''),
            'type' => $request->input('type', ''),
            'dsize' => $request->input('dsize', ''),
            'download' => $request->input('download', ''),
        ];

        if (!empty($params['download'])) {
            $disk = Storage::disk('local');
            $sid = $params['download'];
            $fpath = '/downloads/'.$sid.'.zip';
            if (!$disk->exists($fpath)) {
                $cmd = 'moveResultFile';
                if (!SshConnection::RunAsLocalUser($cmd, $sid)) {
                    return \App::abort(404);
                }
            }
        }

        return view('home', $params);
    }
}
