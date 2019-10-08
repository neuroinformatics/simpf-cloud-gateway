<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use SimPF\Utility\DesktopManager;

class DispatchController extends Controller
{
    public function __invoke(Request $request)
    {
        $resolutions = [
            '1600x1200', '1280x1024', '1024x768', '800x600', // 4x3
            '1920x1080', '1600x900', '1366x768', // 16x9
            '3840x2160', // 4k
        ];
        $type = $request->input('type') ?: 'centos5';
        $url = $request->input('url');
        $dsize = $request->input('dsize') ?: '1366x768';
        if (empty($url)) {
            return $this->error('invalid url parameter');
        }
        if (!in_array($dsize, $resolutions)) {
            return $this->error('invalid dsize parameter');
        }

        $ret = DesktopManager::dispatch($type, $url, $dsize);
        if (false === $ret['sid']) {
            return $this->error($ret['error']);
        }

        $result = ['sid' => $ret['sid']];

        return $this->success($result);
    }

    /**
     * get success response.
     *
     * @param string $message success message
     *
     * @return array response
     */
    private function success($message)
    {
        return ['status' => 'SUCCESS', 'result' => $message];
    }

    /**
     * get error response.
     *
     * @param string $message error message
     *
     * @return array response
     */
    private function error($message)
    {
        return ['status' => 'ERROR', 'result' => $message];
    }
}
