<?php

namespace Cabinet;

/**
 * A filestore implementation that uses a flat filesystem store
 */
class FilesystemFileStore extends AbstractFileStore
{
    /**
     * @var string path to store files
     */
    protected $_filepath;

    /**
     * The number of characters used in the path stubs
     */
    const STUB_KEY_LENGTH = 2;

    /**
     * @param string $path path to store files
     */
    public function __construct($path)
    {
        $this->_filepath = rtrim($path,'/').'/';

        // ensure filestore path exists
        if (!is_dir($path)) FileHelper::createDirectory($path);
    }

    /**
     * Generate the actual filesystem path for a filekey
     */
    protected function _getRealFilePath($filekey, $createDir=true)
    {
        $filekey = substr(sha1($filekey), 0, 32);
        $path = sprintf('%s/%s/',
            rtrim($this->_filepath,'/'),
            substr($filekey,0,self::STUB_KEY_LENGTH)
        );

        // ensure directory path exists
        if ($createDir && !is_dir($path)) {
            FileHelper::createDirectory($path);
        }

        return $path.$filekey;
    }

    /* (non-phpdoc)
     * @see Cabinet\FileStore::newFile
     */
    public function newFile($filekey)
    {
        return $this->_addOpenFile(
            $filekey,
            fopen($this->_getRealFilePath($filekey), 'w+')
        );
    }

    /* (non-phpdoc)
     * @see Cabinet\FileStore::openFile
     */
    public function openFile($filekey,$readOnly=false)
    {
        if ($readOnly) {
            $fp = fopen($this->_getRealFilePath($filekey), 'r');
        } elseif ($this->fileExists($filekey)) {
            $fp = $this->newFile($filekey);
        } else {
            $fp = fopen($this->_getRealFilePath($filekey), 'r+');
            $this->_addOpenFile($filekey, $fp);
        }

        return $fp;
    }

    /* (non-phpdoc)
     * @see Cabinet\FileStore::getFileContents
    */
    public function getFileContents($filekey)
    {
        if (!$this->fileExists($filekey)) {
            throw new FileStoreException("No file for key '$filekey' exists");
        }

        return file_get_contents($this->_getRealFilePath($filekey));
    }

    /* (non-phpdoc)
     * @see Cabinet\FileStore::getFileContents
     */
    public function setFileContents($filekey, $data)
    {
        $filepath = $this->_getRealFilePath($filekey);
        $tmpfilepath = $filepath.'.tmp-'.uniqid();

        // handle streams, copy to the file
        if (is_resource($data)) {
            $fp = fopen($tmpfilepath, 'w+');
            if (!$fp) {
                throw new FileStoreException(
                    "Failed to open '$tmpfilepath' for writing\n" .
                    "Error details:\n" . var_export(error_get_last(), true));
            }

            while (!feof($data)) {
                $buffer = fread($data, 8192);
                $pos = 0;
                while ($pos < strlen($buffer)) {
                    $pos += fwrite($fp, substr($buffer, $pos));
                }
            }
            fclose($fp);
        }
        // handle strings
        else {
            file_put_contents($tmpfilepath, $data);
        }

        // atomically move file
        rename($tmpfilepath, $filepath);

        return true;
    }

    /* (non-phpdoc)
     * @see Cabinet\FileStore::deleteFile
     */
    public function deleteFile($filekey)
    {
        if (!$this->fileExists($filekey)) {
            throw new FileStoreException("No file for key '$filekey' exists");
        }

        if (!unlink($this->_getRealFilePath($filekey))) {
            throw new FileStoreException("File for key '$filekey' couldn't be deleted");
        }

        return true;
    }

    /* (non-phpdoc)
     * @see Cabinet\FileStore::fileExists
     */
    public function fileExists($filekey)
    {
        return file_exists($this->_getRealFilePath($filekey, false));
    }

    /* (non-php)
     * @see Cabinet\FileStore::fileExists()
     */
    public function getFileMetadata($filekey)
    {
        $stats = false;
        $path = $this->_getRealFilePath($filekey, false);

        if ($this->fileExists($filekey)) {
            $stats = array(
                'size' => filesize($path),
                'mtime' => filemtime($path),
                'atime' => fileatime($path),
            );
        }

        return $stats;
    }
}
