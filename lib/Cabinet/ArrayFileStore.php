<?php

namespace Cabinet;

/**
 * A filestore implementation that uses a flat filesystem store
 */
class ArrayFileStore extends AbstractFileStore
{
    private $_array;

    /**
     * Constructor
     */
    public function __construct($array=array())
    {
        $this->_array = $array;
    }

    /* (non-php)
     * @see Cabinet\FileStore::getFileContents()
     */
    public function getFileContents($filekey)
    {
        if (!$this->fileExists($filekey)) {
            throw new FileStoreException("No file for key '$filekey' exists");
        }

        return $this->_array[$filekey];
    }

    /* (non-php)
     * @see Cabinet\FileStore::setFileContents()
     */
    public function setFileContents($filekey,$data)
    {
        $this->_array[$filekey] = $this->_readStreamOrString($data);

        return true;
    }

    /* (non-php)
     * @see Cabinet\FileStore::deleteFile()
     */
    public function deleteFile($filekey)
    {
        if (!$this->fileExists($filekey)) {
            throw new FileStoreException("No file for key '$filekey' exists");
        }

        unset($this->_array[$filekey]);
    }

    /* (non-php)
     * @see Cabinet\FileStore::fileExists()
     */
    public function fileExists($filekey)
    {
        return isset($this->_array[$filekey]);
    }

    /* (non-php)
     * @see Cabinet\FileStore::fileExists()
     */
    public function getFileMetadata($filekey)
    {
        $stats = false;
        if ($this->fileExists($filekey)) {
            $stats = array(
                'size' => strlen($this->_array[$filekey]),
                'mtime' => 0,
                'atime' => time(),
            );
        }

        return $stats;
    }
}
