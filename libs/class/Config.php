<?php

namespace SimPF;

class Config
{
    /**
     * config values.
     *
     * @var array
     */
    private static $mConfigs = false;

    /**
     * constructor.
     */
    protected function __construct()
    {
    }

    /**
     * load configulation file.
     */
    private static function load()
    {
        $fpath = dirname(__DIR__).'/etc/config.json';
        $json = file_get_contents($fpath);
        static::$mConfigs = json_decode($json, true);
    }

    /**
     * get config value.
     *
     * @param string $key
     *
     * @return mixed
     */
    public static function get($key)
    {
        if (false === static::$mConfigs) {
            static::load();
        }

        return static::$mConfigs[$key];
    }

    /**
     * get command file path.
     *
     * @param string $fpath
     *
     * @return string
     */
    public static function getCommandPath($fpath)
    {
        return dirname(__DIR__).'/bin/'.$fpath;
    }

    /**
     * get resource file path.
     *
     * @param string $fpath
     *
     * @return string
     */
    public static function getFilePath($fpath)
    {
        return dirname(__DIR__).'/etc/'.$fpath;
    }

    /**
     * get resource file contents.
     *
     * @param string $fpath
     *
     * @return string
     */
    public static function getFileContents($fpath)
    {
        return file_get_contents(static::getFilePath($fpath));
    }
}
