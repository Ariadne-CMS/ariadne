<?php

/*
 * This file is part of the Ariadne Component Library.
 *
 * (c) Muze <info@muze.nl>0
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace arc\cache;

/**
 * Class FileStore
 * @package arc\cache
 */
class FileStore
{
    protected $root = null;
    protected $currentPath = null;
    protected $basePath = null;
    protected $mode = null;

    /**
     * @param        $root          The root directory to store cache images
     * @param string $currentPath   The path within the root to use
     * @param int    $mode          The filemode to use
     */
    public function __construct($root, $currentPath = '/', $mode = 0770)
    {
        $this->root = $root;
        $this->currentPath = $currentPath;
        $this->basePath = $root . \arc\path::collapse( $currentPath );
        $this->mode = $mode;
    }

    /**
     * Returns the path for an image based on the current path and the name given
     * @param $name     The name for the cache image. The name is base64 encoded, so you cannot use full paths, only filenames.
     * @return string
     */
    protected function getPath($name)
    {
        return $this->basePath . base64_encode( $name );
    }

    /**
     * Returns the contents for a cached image, if it exists, null otherwise.
     * @param string $name
     * @return string|null
     */
    public function getVar($name)
    {
        $filePath = $this->getPath( $name );
        if (file_exists( $filePath )) {
            return file_get_contents( $filePath );
        }
    }

    /**
     * Store a value as a cached image.
     * @param string $name
     * @param string $value
     * @return int
     */
    public function putVar($name, $value)
    {
        $filePath = $this->getPath( $name );
        $dir = dirname( $filePath );
        if (!file_exists( $dir )) {
            mkdir( $dir, $this->mode, true ); //recursive
        }

        return file_put_contents( $filePath, $value, LOCK_EX );
    }

    /**
     * Return a fileinfo array (size, ctime, mtime) for a cached image, or null if it isn't found.
     * @param $name
     * @return array|null
     */
    public function getInfo($name)
    {
        $filePath = $this->getPath( $name );
        if (file_exists( $filePath ) && is_readable( $filePath )) {
            return array(
                'size' => filesize($filePath),
                'ctime' => filectime( $filePath ),
                'mtime' => filemtime( $filePath )
            );
        } else {
            return null;
        }
    }

    /**
     * Change the file info, only supports mtime in this implementation. Returns true if the cache image is found.
     * @param string $name The name of the cache image
     * @param array  $info The new file information - an array with 'mtime','size' and/or 'ctime' keys.
     * @return bool
     */
    public function setInfo($name, $info)
    {
        $filePath = $this->getPath( $name );
        if (file_exists( $filePath ) && is_readable( $filePath )) {
            foreach ($info as $key => $value) {
                switch ($key) {
                    case 'mtime':
                        touch( $filePath, $value );
                        break;
                    case 'size':
                    case 'ctime':
                        // FIXME: ignore silently? other storage mechanisms might need this set explicitly?
                        break;
                }
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * Change the path to store/retrieve cache images.
     * @param $path
     * @return FileStore
     */
    public function cd($path)
    {
        return new FileStore( $this->root, \arc\path::collapse( $path, $this->currentPath ), $this->mode );
    }

    /**
     * Returns an array with cache image names in the current path.
     * @return array
     */
    public function ls()
    {
        $dir = dir( $this->basePath );
        $result = array();
        if ($dir) {
            while ($name = $dir->read()) {
                if (!is_dir($this->basePath . $name )) {
                    $name = base64_decode($name);
                }
                $result[] = $name;
            }
            $dir->close();
        }

        return $result;
    }

    /**
     * Remove a cache image.
     * @param $name
     * @return bool
     */
    public function remove($name)
    {
        $filePath = $this->getPath( $name );

        return unlink( $filePath );
    }

    /**
     * @param $dir
     */
    protected function cleanup($dir)
    {
        foreach (glob( $dir . '/*' ) as $file) {
            if (is_dir( $file )) {
                $this->cleanup( $file );
            } else {
                unlink( $file );
            }
        }
        rmdir( $dir );
    }

    /**
     * Removes an entire subtree of cache images.
     * @param string $name The name of the image / subdir to remove.
     * @return bool
     */
    public function purge($name = '')
    {
        if ($name) {
            $this->remove( $name );
        }
        $dirPath = $this->basePath . \arc\path::collapse( $name );
        if (file_exists( $dirPath ) && is_dir( $dirPath )) {
            $this->cleanup( $dirPath );
        }

        return true;
    }

    /**
     * Locks a cache image. Default a write only lock, so you can still read the cache.
     * @param string $name
     * @param bool $blocking
     * @return bool
     */
    public function lock($name, $blocking = false)
    {
        $filePath = $this->getPath( $name );
        $dir = dirname( $filePath );
        if (!file_exists( $dir )) {
            mkdir( $dir, $this->mode, true ); //recursive
        }
        $lockFile = fopen( $filePath, 'c' );
        $lockMode = LOCK_EX;
        if (!$blocking) {
            $lockMode = $lockMode | LOCK_NB;
        }

        return flock( $lockFile, $lockMode );
    }

    /**
     * Unlocks a cache image.
     * @param $name
     * @return bool
     */
    public function unlock($name)
    {
        $filePath = $this->getPath( $name );
        $lockFile = fopen( $filePath, 'c' );

        return flock( $lockFile, LOCK_UN);
    }
}
