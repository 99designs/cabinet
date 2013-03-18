<?php

namespace Cabinet;

/**
 *
 */
class MountPointFileStoreTest
	extends \PHPUnit_Framework_TestCase
{
	/**
	 * Creates and registers a mock filestore with the factory
	 */
	private function _createMockFileStore($key)
	{
		return \Mockery::mock('\Cabinet\MountPointFileStore');
	}

	public function testRootMount()
	{
		$testfs = $this->_createMockFileStore('testfs');
		$anotherfs = $this->_createMockFileStore('anotherfs');

		$testfs
			->shouldReceive('setFileContents')
			->once()
			->with('blargh','meh');

		$anotherfs
			->shouldReceive('setFileContents')
			->never();

		// test that the / prefix routes to our testfs object
		$fs = new MountPointFileStore();
		$fs->mount('/', $testfs);
		$fs->mount('/another',$anotherfs);

		$fs->setFileContents('/blargh','meh');
	}

	public function testExceptionWhenNoMatchingMount()
	{
		$testfs = $this->_createMockFileStore('testfs');
		$testfs
			->shouldReceive('setFileContents')
			->never();

		$fs = new MountPointFileStore();
		$fs->mount('/no/match',$testfs);

		$this->setExpectedException('\Cabinet\FileStoreException');
		$fs->setFileContents('/blargh','meh');
	}

	public function testLeadingSlashIsTrimmed()
	{
		$testfs = $this->_createMockFileStore('testfs');
		$testfs
			->shouldReceive('setFileContents')
			->once()
			->with('meep','meh');

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

		$testfs
			->shouldReceive('setFileContents')
			->once()
			->with('meep','meh');
		$anotherfs
			->shouldReceive('setFileContents')
			->never();
		$yetanotherfs
			->shouldReceive('setFileContents')
			->never();

		// test that the / prefix routes to our testfs object
		$fs = new MountPointFileStore();
		$fs->mount('/my/match', $testfs);
		$fs->mount('/my',$anotherfs);
		$fs->mount('/my/ma', $yetanotherfs);

		$fs->setFileContents('/my/match/meep','meh');
	}
}
