<?php

namespace SimPF;

class Downloader
{
    /**
     * config.
     *
     * @var array
     */
    protected $mConfig;

    /**
     * construct.
     *
     * @param array $config downloader configs
     */
    public function __construct($config)
    {
        $this->mConfig = $config;
    }

    /**
     * download file.
     *
     * @param string $url
     * @param string $outputDir
     *
     * @return string|bool
     */
    public function download($url, $outputDir)
    {
        $ret = false;
        $outputDir = rtrim($outputDir, '/');
        if (!is_writable($outputDir)) {
            return false;
        }
        if (preg_match('/^demo:.+$/', $url)) {
            $ret = $this->_downloadDemo($url, $outputDir);
        } elseif (preg_match('/^simpf:.+$/', $url)) {
            $ret = $this->_downloadSimPF($url, $outputDir);
        } elseif (preg_match('/\/modules\/xoonips\/detail.php/', $url)) {
            $ret = $this->_downloadXooNIps($url, $outputDir);
        } elseif (preg_match('/^(?:ftp|https?):.+$/', $url)) {
            $ret = $this->_downloadCurl($url, $outputDir);
        }

        return $ret;
    }

    /**
     * download offline demo deta.
     *
     * @param string $url
     * @param string $outputDir
     *
     * @return string|bool
     */
    protected function _downloadDemo($url, $outputDir)
    {
        if (!preg_match('/^demo:([a-zA-Z0-9_\-\.]+)$/', $url, $matches)) {
            return false;
        }
        $fname = $matches[1];
        $fpath = $this->mConfig['offline'].'/demo/'.$fname;
        if (file_exists($fpath)) {
            $outputFile = $outputDir.'/'.$fname;
            if (@copy($fpath, $outputFile)) {
                return $outputFile;
            }
        }

        return false;
    }

    /**
     * download offline simpf deta.
     *
     * @param string $url
     * @param string $outputDir
     *
     * @return string|bool
     */
    protected function _downloadSimPF($url, $outputDir)
    {
        if (!preg_match('/^simpf:([a-zA-Z0-9]+\/\d+\/[a-zA-Z0-9_\-\.]+)$/', $url, $matches)) {
            return false;
        }
        $fname = $matches[1];
        $fpath = $this->mConfig['offline'].'/simpf/'.$fname;
        if (file_exists($fpath)) {
            $outputFile = $outputDir.'/'.basename($fname);
            if (@copy($fpath, $outputFile)) {
                return $outputFile;
            }
        }

        return false;
    }

    /**
     * download xoonips deta.
     *
     * @param string $url
     * @param string $outputDir
     *
     * @return string
     */
    protected function _downloadXooNIps($url, $outputDir)
    {
        $uname = $this->mConfig['xoonips3']['username'];
        $pass = $this->mConfig['xoonips3']['password'];
        $apiUrl = preg_replace('/detail\.php.*/', 'xoonipsapi.php', $url);
        $itemId = 0;
        $idType = '';
        if (preg_match('/detail\.php\?item_id=(\d+)(?:&.*)*$/', $url, $matches)) {
            $itemId = (int) $matches[1];
            $idType = 'item_id';
        } elseif (preg_match('/detail\.php\?(?:[a-zA-Z0-9_]*)id=([^&]+)(?:&.*)*$/', $url, $matches)) {
            $itemId = $matches[1];
            $idType = 'ext_id';
        }
        if ('' == $idType) {
            return false;
        }

        $c = new XooNIps3\Client($apiUrl);
        if (!$c->login($uname, $pass)) {
            return false;
        }

        $item = $c->getItem($itemId, $idType);
        if (false === $item) {
            $c->logout();

            return false;
        }

        $itemtypeId = $item['itemtype'];
        $itemtype = $c->getItemtype($itemtypeId);
        if (false === $itemtype) {
            $c->logout();

            return false;
        }

        $mainfile = $itemtype['mainfile'];
        if ('' == $mainfile) {
            $c->logout();

            return false;
        }
        $fileId = 0;
        foreach ($item['detail_field'] as $detailField) {
            if ('detail_field.'.$detailField['name'] == $mainfile) {
                $fileId = (int) $detailField['value'];
                break;
            }
        }
        if (0 == $fileId) {
            $c->logout();

            return false;
        }

        // copy data from cache directory if exists
        $xoopsUrl = preg_replace('/^https?:\/\/(.+)\/modules\/xoonips\/xoonipsapi.php$/', '\1', $apiUrl);
        $sourceDir = false;
        if ($sourceDir && is_dir($sourceDir)) {
            $fileMeta = $c->getFileMetadata($fileId);
            $sourceFile = $sourceDir.'/'.$fileMeta['id'];
            $outputFile = $outputDir.'/'.$fileMeta['originalname'];
            if (@copy($sourceFile, $outputFile)) {
                $c->logout();

                return $outputFile;
            }
        }

        $file = $c->getFile($fileId, true);
        if (false === $file) {
            $c->logout();

            return false;
        }
        $c->logout();

        $outputFile = $outputDir.'/'.$file['originalname'];
        if (!($fp = fopen($outputFile, 'w'))) {
            return false;
        }
        fwrite($fp, $file['data']);
        fclose($fp);

        return $outputFile;
    }

    /**
     * download deta by curl.
     *
     * @param string $url
     * @param string $outputDir
     *
     * @return string
     */
    protected function _downloadCurl($url, $outputDir)
    {
        $parsedUrl = parse_url($url);
        $uname = isset($parsedUrl['user']) ? $parsedUrl['user'] : '';
        $pass = isset($parsedUrl['pass']) ? $parsedUrl['pass'] : '';
        $fname = isset($parsedUrl['path']) ? basename($parsedUrl['path']) : 'unknown.dat';
        $isFtp = 'ftp' == $parsedUrl['scheme'] ? true : false;
        $outputFile = $outputDir.'/'.$fname;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        if ('' != $uname && '' != $pass) {
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_USERPWD, $uname.':'.$pass);
        }
        $renameFile = '';
        if ($isFtp) {
            curl_setopt($curl, CURLOPT_HEADER, false);
        } else {
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_AUTOREFERER, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_FAILONERROR, true);
            curl_setopt(
                $curl,
                CURLOPT_HEADERFUNCTION,
                function ($ch, $header) use ($outputDir, &$renameFile) {
                    if (preg_match('/^Content-Disposition:\s+(?:attachment|inline);\s+filename(?:=(.+?)|\*=UTF-8\'\'(\S+))\s*$/i', $header, $matches)) {
                        $fname = (isset($matches[1]) && '' !== $matches[1]) ? $matches[1] : $matches[2];
                        $renameFile = $outputDir.'/'.urldecode(trim($fname, '"'));
                    }

                    return strlen($header);
                }
            );
        }
        if (!($fp = fopen($outputFile, 'w'))) {
            return false;
        }
        curl_setopt($curl, CURLOPT_FILE, $fp);
        curl_setopt($curl, CURLOPT_TIMEOUT, 180);
        $ret = curl_exec($curl);
        fclose($fp);
        if (false === $ret) {
            unlink($outputFile);

            return false;
        }
        if (!$isFtp) {
            $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if (200 !== $code) {
                unlink($outputFile);

                return false;
            }
        }
        curl_close($curl);
        if ('' != $renameFile) {
            rename($outputFile, $renameFile);
            $outputFile = $renameFile;
        }

        return $outputFile;
    }
}
