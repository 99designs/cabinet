<?php

namespace Cabinet;

class FileHelper
{
	/**
	 * A recursive version of the glob function
	 * @see http://www.php.net/manual/function.glob.php
	 *
	 * @param string $dir      Directory to start with.
	 * @param string $pattern  Pattern to glob for.
	 * @param int $flags      Flags sent to glob.
	 * @return array containing all pattern-matched files.
	 */
	public static function globr($dir, $pattern = '*', $flags = NULL)
	{
		if(empty($dir) || !is_dir($dir))
		{
			throw new \Exception("Unable to glob $dir, not a directory");
		}

		$dir = escapeshellcmd(rtrim($dir,'/'));
		$aFiles = glob("$dir/$pattern", $flags);

		foreach (glob("$dir/*", GLOB_ONLYDIR) as $sSubDir)
		{
			$aSubFiles = self::globr($sSubDir, $pattern, $flags);
			$aFiles = array_merge($aFiles, $aSubFiles);
		}

		return $aFiles;
	}

	/**
	 * Recursively delete a directory and it's contents. Use with caution!
	 */
	public static function deleteDirectory($dir,$later=false)
	{
		if(empty($dir) || !is_dir($dir))
		{
			throw new \SpfException("Unable to delete $dir, not a directory");
		}

		if($later)
		{
			register_shutdown_function(array(__CLASS__, 'deleteDirectory'),$dir);
		}
		else
		{
			self::walkDirectory($dir,array(__CLASS__,'_deleteCallback'), array($dir));
			if(is_dir($dir)) rmdir($dir);
		}

		return true;
	}

	/**
	 * Recursively create a directory, also creates any parent directories that are required.
	 */
	public static function createDirectory($directory, $umask=0777)
	{
		if(is_dir($directory)) return true;

		if(!@mkdir($directory, $umask, true))
			throw new \SpfException("Unable to create $directory");

		return true;
	}

	/**
	 * Recursively copy a directory
	 */
	public static function copyDirectory($srcdir, $dstdir, $copyhidden=false)
	{
		return self::walkDirectory($srcdir,array(__CLASS__,'_copyCallback'),
			array($srcdir,$dstdir),$copyhidden);
	}

	/**
	 * Iterates through all the files and directories within a particular directory, passes
	 * them to a callback function along with the provided params array. Returns a count of all
	 * the files and directories that were successfully operated on.
	 */
	public static function walkDirectory($dir, $callback, $params=array(), $hidden=true)
	{
		$files = $hidden ? self::globr($dir, '{,.}*', GLOB_BRACE) : self::globr($dir);

		// filter out unneeded files
		$files = array_filter($files,array(__CLASS__,'_filterDotFiles'));

		rsort($files);
		$counter = 0;

		foreach($files as $file)
		{
			if(call_user_func($callback, $file, $params)) $counter++;
		}

		return $counter;
	}

	/**
	 * A callback that filters hidden files, currently files starting with a dot and paths
	 * containing MacOSX hidden files are filtered out
	 */
	private static function _filterHiddenFiles($file)
	{
		return !preg_match('#(?<=/)(__MACOSX|\..+?)#',$file);
	}

	/**
	 * A callback for filtering out . and .. from file lists
	 */
	private static function _filterDotFiles($file)
	{
		return (basename($file) != '.' && basename($file) != '..');
	}

	private static function _copyCallback($filename, $params)
	{
		$destpath = rtrim($params[1],'/')."/".ltrim(substr($filename, strlen($params[0])),'/');

		if(is_file($filename))
		{
			if(!is_dir(dirname($destpath)))
			{
				self::createDirectory(dirname($destpath));
			}

			return copy($filename,$destpath);
		}
		else if(is_dir($filename))
		{
			return self::createDirectory($destpath);
		}
	}

	private static function _deleteCallback($filename, $params)
	{
		if(is_file($filename))
		{
			unlink($filename);
			@rmdir(dirname($filename));
		}
		else if(is_dir($filename))
		{
			@rmdir($filename);
		}
	}
}

