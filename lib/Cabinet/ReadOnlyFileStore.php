<?php

namespace Cabinet;

/**
 * A decorator for a filestore that delegates writes to a secondary filestore,
 * defaulting to a null filestore.
 */
class ReadOnlyFileStore implements FileStore
{
    private $_writer;
    private $_reader;

    /**
     * Constructor
     */
    public function __construct($readDelegate, $writeDelegate=null)
    {
        $this->_reader = $readDelegate;
        $this->_writer = $writeDelegate ?: new \Cabinet\NullFileStore();
    }

    // ----------------------------------------------------
    // read operations

    /* (non-phpdoc)
     * @see Cabinet\FileStore::getFileContents
    */
    public function getFileContents($filekey)
    {
        if ($this->_writer->fileExists($filekey)) {
            return $this->_writer->getFileContents($filekey);
        } else {
            return $this->_reader->getFileContents($filekey);
        }
    }

    /* (non-phpdoc)
     * @see Cabinet\FileStore::fileExists
    */
    public function fileExists($filekey)
    {
        return $this->_writer->fileExists($filekey) ||
            $this->_reader->fileExists($filekey);
    }

    /* (non-phpdoc)
     * @see Cabinet\FileStore::getFileMetadata
    */
    public function getFileMetadata($filekey)
    {
        if ($this->_writer->fileExists($filekey)) {
            return $this->_writer->getFileMetadata($filekey);
        } else {
            return $this->_reader->getFileMetadata($filekey);
        }
    }

    /* (non-phpdoc)
     * @see Cabinet\FileStore::downloadFile
    */
    public function downloadFile($filekey,$filepointer)
    {
        if ($this->_writer->fileExists($filekey)) {
            return $this->_writer->downloadFile($filekey, $filepointer);
        } else {
            return $this->_reader->downloadFile($filekey, $filepointer);
        }
    }

    // ----------------------------------------------------
    // write operations

    /* (non-phpdoc)
     * @see Cabinet\FileStore::newFile
    */
    public function newFile($filekey)
    {
        return $this->_writer->newFile($filekey);
    }

    /* (non-phpdoc)
     * @see Cabinet\FileStore::openFile
    */
    public function openFile($filekey, $readOnly=false)
    {
        // if readonly, and doesn't exist locally, defer to reader
        if ($readOnly  && !$this->_writer->fileExists($filekey)) {
            return $this->_reader->openFile($filekey,true);
        }
        // if not readonly and doesn't exist locally, copy it :(
        else if (!$readOnly && !$this->_writer->fileExists($filekey)) {
            $fp = $this->_writer->newFile($filekey);
            $this->_reader->downloadFile($filekey, $fp);
            $this->_writer->close($fp);
        }

        // otherwise just use the local writer
        return $this->_writer->openFile($filekey, $readOnly);
    }

    /* (non-phpdoc)
     * @see Cabinet\FileStore::getFileContents
    */
    public function setFileContents($filekey, $data)
    {
        return $this->_writer->setFileContents($filekey, $data);
    }

    /* (non-phpdoc)
     * @see Cabinet\FileStore::deleteFile
     */
    public function deleteFile($filekey)
    {
        // delete won't be permenant, as it will be cached again on the next read
        if (!$this->_writer->fileExists($filekey)
            && $this->_reader->fileExists($filekey)) {
            return;
        }

        return $this->_writer->deleteFile($filekey);
    }

    /* (non-phpdoc)
     * @see Cabinet\FileStore::close
     */
    public function close($fp)
    {
        $this->_writer->close($fp);
    }
}
