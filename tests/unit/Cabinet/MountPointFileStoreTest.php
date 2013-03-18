<?php

namespace Cabinet;

\Mock::generate('\Cabinet\FileStore','MockFileStore');

/**
 *
 */
class MountPointFileStoreTest extends \Contests_Testing_UnitTestCase
{
	/**
	 * Creates and registers a mock filestore with the factory
	 */
	private function _createMockFileStore($key)
	{
		return new \MockFileStore($this);
	}

	public function testRootMount()
	{
		$testfs = $this->_createMockFileStore('testfs');
		$anotherfs = $this->_createMockFileStore('anotherfs');

		$testfs->expectOnce('setFileContents',array('blargh','meh'));
		$anotherfs->expectNever('setFileContents');

		// test that the / prefix routes to our testfs object
		$fs = new MountPointFileStore();
		$fs->mount('/', $testfs);
		$fs->mount('/another',$anotherfs);

		$fs->setFileContents('/blargh','meh');
	}

	public function testExceptionWhenNoMatchingMount()
	{
		$testfs = $this->_createMockFileStore('testfs');
		$testfs->expectNever('setFileContents');

		$fs = new MountPointFileStore();
		$fs->mount('/no/match',$testfs);

		try
		{
			$fs->setFileContents('/blargh','meh');
			$this->fail("Expected exception");
		}
		catch(FileStoreException $e)
		{
			$this->assertTrue(true);
		}
	}

	public function testLeadingSlashIsTrimmed()
	{
		$testfs = $this->_createMockFileStore('testfs');
		$testfs->expectOnce('setFileContents',array('meep','meh'));

		// test that the / prefix routes to our testfs object
		$fs = new MountPointFileStore();
		$fs->mount('/my/match', $testfs);

		$fs->setFileContents('my/match/meep','meh');
	}

	public function testLongestMatchIsPicked()
	{
		$testfs = $this->_createMockFileStore('testfs');
		$anotherfs = $this->_createMockFileStore('anotherfs');
		$yetanotherfs = $this->_createMockFileStore('anotherfs');

		$testfs->expectOnce('setFileContents',array('meep','meh'));
		$anotherfs->expectNever('setFileContents');
		$yetanotherfs->expectNever('setFileContents');

		// test that the / prefix routes to our testfs object
		$fs = new MountPointFileStore();
		$fs->mount('/my/match', $testfs);
		$fs->mount('/my',$anotherfs);
		$fs->mount('/my/ma', $yetanotherfs);

		$fs->setFileContents('/my/match/meep','meh');
	}
}

