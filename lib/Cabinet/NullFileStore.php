<?php

namespace Cabinet;

/**
 * A null filestore which discards any writes to it and appears totally empty
 */
class NullFileStore implements FileStore
{
    /* (non-phpdoc)
     * @see Cabinet\FileStore::newFile
     */
    public function newFile($filekey)
    {
        return fopen('php://temp/maxmemory:512000', 'r+');
    }

    /* (non-phpdoc)
     * @see Cabinet\FileStore::openFile
     */
    public function openFile($filekey, $readOnly=false)
    {
        return fopen('php://temp/maxmemory:512000', 'rb+');
    }

    /* (non-phpdoc)
     * @see Cabinet\FileStore::getFileContents
    */
    public function getFileContents($filekey)
    {
        return null;
    }

    /* (non-phpdoc)
     * @see Cabinet\FileStore::getFileContents
    */
    public function setFileContents($filekey, $data, $options = null)
    {
        return true;
    }

    /* (non-phpdoc)
     * @see Cabinet\FileStore::deleteFile
     */
    public function deleteFile($filekey)
    {
        return false;
    }

    /* (non-phpdoc)
     * @see Cabinet\FileStore::fileExists
     */
    public function fileExists($filekey)
    {
        return false;
    }

    /* (non-phpdoc)
     * @see Cabinet\FileStore::close
     */
    public function close($filepointer)
    {
    }

    /* (non-phpdoc)
     * @see Cabinet\FileStore::getFileMetadata
     */
    public function getFileMetadata($filekey)
    {
        return array();
    }

    /* (non-phpdoc)
     * @see Cabinet\FileStore::getFileMetadata
     */
    public function downloadFile($filekey, $filepointer)
    {
    }
}
