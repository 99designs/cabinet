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
	function newFile($filekey)
	{
		return fopen('php://temp/maxmemory:512000','r+');
	}

	/* (non-phpdoc)
	 * @see Cabinet\FileStore::openFile
	 */
	function openFile($filekey,$readOnly=false)
	{
		return fopen('php://temp/maxmemory:512000','rb+');
	}

	/* (non-phpdoc)
	 * @see Cabinet\FileStore::getFileContents
	*/
	function getFileContents($filekey)
	{
		return NULL;
	}

	/* (non-phpdoc)
	 * @see Cabinet\FileStore::getFileContents
	*/
	function setFileContents($filekey,$data)
	{
		return true;
	}

	/* (non-phpdoc)
	 * @see Cabinet\FileStore::deleteFile
	 */
	function deleteFile($filekey)
	{
		return false;
	}

	/* (non-phpdoc)
	 * @see Cabinet\FileStore::fileExists
	 */
	function fileExists($filekey)
	{
		return false;
	}

	/* (non-phpdoc)
	 * @see Cabinet\FileStore::close
	 */
	function close($filepointer)
	{
	}

	/* (non-phpdoc)
	 * @see Cabinet\FileStore::getFileMetadata
	 */
	function getFileMetadata($filekey)
	{
		return array();
	}

	/* (non-phpdoc)
	 * @see Cabinet\FileStore::getFileMetadata
	 */
	function downloadFile($filekey,$filepointer)
	{
	}
}

