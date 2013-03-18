<?php

namespace Cabinet;

/**
 * An abstract filestore that provides the bulk of a file store base on the
 * php://temp stream available in PHP 5.1.0.
 *
 * Writing extensions of this class is easy, simply write implementations for
 * getFileContents, setFileContents, fileExists, delete and getFileMetadata.

 * @author Lachlan Donald <lachlan@sitepoint.com>
 */
abstract class AbstractFileStore implements FileStore
{
	const MAX_MEMORY = '512000'; // 500Kb
	private $_files = array();

	/**
	 * Creates a temporary file pointer that can optionally be written to the
	 * backend when close() is called on it.

	 * @param $filekey string the key of the file
	 */
	protected function _createTempFile($filekey, $writeonclose=true)
	{
		if(!$fp = fopen('php://temp/maxmemory:'.self::MAX_MEMORY, 'w+'))
		{
			throw new \Exception("Failed to create temp file stream");
		}

		// optionally track the file for later closing
		if($writeonclose) $this->_addOpenFile($filekey, $fp);

		return $fp;
	}

	/**
	 * Adds a file to the internal file table, for later closing and auto-close
	 * in the destructor, files with 'r' mode aren't closed.
	 * @param $filekey string the key of the file
	 * @param $fp resource the file pointer
	 * @param $mode string the mode that the file was opened in
	 * @return the file pointer added
	 */
	protected function _addOpenFile($filekey, $fp)
	{
		$this->_files[strval($fp)] = array($fp,$filekey);
		return $fp;
	}

	/**
	 * Either reads the contents of a stream or a string, returns a string
	 */
	protected function _readStreamOrString($mixed)
	{
		return is_resource($mixed) ? stream_get_contents($mixed) : $mixed;
	}

	/* (non-phpdoc)
	 * @see Cabinet\FileStore::newFile
	*/
	public function newFile($filekey)
	{
		return $this->_createTempFile($filekey);
	}

	/* (non-phpdoc)
	 * @see Cabinet\FileStore::openFile
	 */
	public function openFile($filekey,$readOnly=false)
	{
		$fp = $this->_createTempFile($filekey, !$readOnly);

		// populate the temp file with contents
		fwrite($fp, $this->getFileContents($filekey));
		rewind($fp);

		return $fp;
	}

	/* (non-phpdoc)
	 * @see Cabinet\FileStore::close
	 */
	public function close($fp)
	{
		$fpkey = strval($fp);

		if(isset($this->_files[$fpkey]))
		{
			rewind($fp);

			// flush to the backend
			$this->setFileContents($this->_files[$fpkey][1], $fp);

			// close the pointer
			fclose($fp);
			unset($this->_files[$fpkey]);
		}
	}

	/* (non-php)
	 * @see Cabinet\FileStore::downloadFile()
	 */
	public function downloadFile($filekey, $filepointer)
	{
		$fp = $this->openFile($filekey);
		stream_copy_to_stream($fp, $filepointer);
		$this->close($fp);
	}

	/**
	 * Destructor, close open files
	 */
	function __destruct()
	{
		try
		{
			foreach($this->_files as $entry)
			{
				// only close streams that haven't been closed
				if(stream_get_meta_data($entry[0]))
				{
					$this->close($entry[0]);
				}
			}
		}
		catch(Exception $e)
		{
			error_log($e->getMessage());
		}
	}
}

