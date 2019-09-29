<?php

namespace SimPF;

class DirectoryUtils
{
    /**
     * remove file recursive.
     *
     * @param string $dir target directory
     */
    public static function removeRecursive($dir)
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($iterator as $item) {
            if ($item->isDir()) {
                if (false === @rmdir($item->getPathname())) {
                    return false;
                }
            } else {
                if (false === @unlink($item->getPathname())) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * copy file recursive.
     *
     * @param string $srcdir source directory
     * @param string $dstdir dest directory
     */
    public static function copyRecursive($srcdir, $dstdir)
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($srcdir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($iterator as $item) {
            if ($item->isDir()) {
                if (false === @mkdir($dstdir.DIRECTORY_SEPARATOR.$iterator->getSubPathname())) {
                    return false;
                }
            } else {
                $srcfile = $item->getPathname();
                $dstfile = $dstdir.DIRECTORY_SEPARATOR.$iterator->getSubPathname();
                if (false === @copy($srcfile, $dstfile)) {
                    return false;
                }
                $st = stat($srcfile);
                @chmod($dstfile, $st['mode']);
            }
        }

        return true;
    }
}
