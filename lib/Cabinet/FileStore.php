<?php

namespace Cabinet;

/**
 * Provides a simple interface to a store of files, referenced by keys.
 */
interface FileStore
{
    /**
     * Creates a new file and returns a pointer to it. This file is only written
     * when {@link Cabinet\FileStore::close} is called with the pointer. If the
     * file exists, it will be truncated and overwritten.
     *
     * @param string $filekey the path to the file in the filestore
     * @return Resource file pointer for the new file, file opened in write mode
     * @throws Cabinet\FileStoreException on error
     */
    public function newFile($filekey);

    /**
     * Open a file based on the key. This file is only written
     * when {@link Cabinet\FileStore::close} is called with the pointer. If the
     * mode is not a read-only mode, the file will be created if it doesn't
     * already exist with {@link newFile}. The file pointer will be returned at
     * the start of the stream.
     *
     * @param string $filekey the path to the file in the filestore
     * @param boolean $readOnly whether to open the file in a read-only mode
     * @return Resource file pointer for the opened file, at the start
     * @throws Cabinet\FileStoreException on error
     */
    public function openFile($filekey, $readOnly = false);

    /**
     * Gets the contents of a file as a string
     *
     * @param string $filekey the path to the file in the filestore
     * @throws Cabinet\FileStoreException If file with that filekey doesn't exist
     * @return string the contents of a file
     */
    public function getFileContents($filekey);

    /**
     * Sets the contents of a file directly, regardless of whether it currently
     * exists. Data can be either a string or a stream resource.
     *
     * @param string $filekey the path to the file in the filestore
     * @param mixed  $data    file data
     * @param array  $options optional data e.g. content-type
     * @throws Cabinet\FileStoreException If the file cannot be written
     * @return boolean success
     */
    public function setFileContents($filekey, $data, $options = null);

    /**
     * Deletes a file by the key
     *
     * @param string $filekey the path to the file in the filestore
     * @return boolean Was file deleted successfully?
     * @throws Cabinet\FileStoreException If file with that filekey does not exist
     */
    public function deleteFile($filekey);

    /**
     * Determines if a file exists
     *
     * @return boolean Does file exist?
     */
    public function fileExists($filekey);

    /**
     * Gets metadata about a file as an array of key=>value pairs, or false if
     * the file doesn't exist. At a minimum the following keys are provided:
     * <pre>
     * size => the file size in bytes
     * </pre>
     * @return array an array of metadata, or false if the file doesn't exist
     */
    public function getFileMetadata($filekey);

    /**
     * Downloads a file into a filepointer
     * @throws Cabinet\FileStoreException If file with that filekey does not exist
     */
    public function downloadFile($filekey, $filepointer);

    /**
     * Closes a file pointer opened with {@link openFile} or {@link newFile}, this
     * triggers a flush.
     */
    public function close($filepointer);
}
