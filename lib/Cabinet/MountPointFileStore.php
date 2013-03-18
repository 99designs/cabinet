<?php

namespace Cabinet;

/**
 * A filestore mediator that allows a filestore to be composed of other filestores
 * bound to certain points in the virtual file system.
 *
 * @author Lachlan Donald <lachlan@99designs.com>
 */
class MountPointFileStore implements FileStore
{
    private $_mounts=array();

    /**
     * Adds a filestore at a specific path
     */
    public function mount($path, $filestore)
    {
        $pattern = ($path == '/')
            ? '#^(/)(.+)$#'
            : '#^('.preg_quote($path, '#').')(/.+)?$#';

        $this->_mounts[$pattern] = $filestore;
    }

    /**
     * Dynamically invoke a method on the matching filestore
     */
    private function _invokeMatchingFilestore($filekey, $method, $params)
    {
        if ($filekey[0] != '/') $filekey = '/'.$filekey;

        $bestMatch = false;
        $bestPattern = false;
        $bestSlashCount = 0;

        // find the best match for a mount point
        foreach ($this->_mounts as $pattern => $fileStore) {
            if (preg_match($pattern, $filekey, $matches)) {
                $slashCount = substr_count($matches[1], '/');

                // only store the match if it's longer than the previous match
                if ($slashCount >= $bestSlashCount) {
                    $bestMatch = $fileStore;
                    $bestPattern = $pattern;
                    $bestSlashCount = $slashCount;
                }
            }
        }

        if (!$bestMatch) {
            throw new FileStoreException("No filestore mounted for $filekey");
        }

        // strip the mount point from the key sent to the filestore
        $params[0] = ltrim(preg_replace($bestPattern, '\\2', $filekey), '/');

        // route the method call to the filestore
        return call_user_func_array(array($bestMatch, $method), $params);
    }

    /* (non-phpdoc)
     * @see Cabinet\FileStore::newFile
     */
    public function newFile($filekey)
    {
        return $this->_invokeMatchingFilestore($filekey,
            __FUNCTION__, array($filekey));
    }

    /* (non-phpdoc)
     * @see Cabinet\FileStore::getFileMetadata
    */
    public function getFileMetadata($filekey)
    {
        return $this->_invokeMatchingFilestore($filekey,
            __FUNCTION__, array($filekey));
    }

    /* (non-phpdoc)
     * @see Cabinet\FileStore::getFileContents
    */
    public function getFileContents($filekey)
    {
        return $this->_invokeMatchingFilestore($filekey,
            __FUNCTION__, array($filekey));
    }

    /* (non-phpdoc)
     * @see Cabinet\FileStore::getFileContents
    */
    public function setFileContents($filekey, $data)
    {
        return $this->_invokeMatchingFilestore($filekey,
            __FUNCTION__, array($filekey,$data));
    }

    /* (non-phpdoc)
     * @see Cabinet\FileStore::deleteFile
     */
    public function deleteFile($filekey)
    {
        return $this->_invokeMatchingFilestore($filekey,
            __FUNCTION__, array($filekey));
    }

    /* (non-phpdoc)
     * @see Cabinet\FileStore::openFile
     */
    public function openFile($filekey, $readOnly=false)
    {
        return $this->_invokeMatchingFilestore($filekey,
            __FUNCTION__, array($filekey, $readOnly));
    }

    /* (non-phpdoc)
     * @see Cabinet\FileStore::fileExists
     */
    public function fileExists($filekey)
    {
        return $this->_invokeMatchingFilestore($filekey,
            __FUNCTION__, array($filekey));
    }

    /* (non-phpdoc)
     * @see Cabinet\FileStore::downloadFile
     */
    public function downloadFile($filekey, $filepointer)
    {
        return $this->_invokeMatchingFilestore($filekey,
            __FUNCTION__, array($filekey));
    }

    /* (non-phpdoc)
     * @see Cabinet\FileStore::close
     */
    public function close($filepointer)
    {
        foreach ($this->_mounts as $key => $filestore) {
            $filestore->close($filepointer);
        }
    }
}
