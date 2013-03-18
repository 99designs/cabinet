<?php

namespace Cabinet;

class ArrayFileStoreTest extends \Contests_Testing_UnitTestCase
{
	function setUp()
	{
		$this->array = array();

		// create an instance
		$this->instance = new ArrayFileStore($this->array);
		$this->assertTrue(is_object($this->instance));
	}

	function testFileCreation()
	{
		$this->assertFalse($this->instance->fileExists('testfile'));
		$filehandle = $this->instance->newFile('testfile');
		$this->assertTrue(is_resource($filehandle));

		// write some stuff
		fwrite($filehandle,"this is a test");
		$this->instance->close($filehandle);

		$this->assertTrue($this->instance->fileExists('testfile'));
	}

	function testFileContentsHelpers()
	{
		$this->assertFalse($this->instance->fileExists('testfile'));
		$this->instance->setFileContents('testfile','mmmmm plastic');
		$this->assertTrue($this->instance->fileExists('testfile'));
		$this->assertEqual('mmmmm plastic',$this->instance->getFileContents('testfile'));
	}

	function testPuttingTwiceWorksOk()
	{
		$this->assertFalse($this->instance->fileExists('testfile'));
		$this->instance->setFileContents('testfile','mmmmm plastic');
		$this->instance->setFileContents('testfile','frogs are green');
		$this->assertTrue($this->instance->fileExists('testfile'));

		$this->assertEqual('frogs are green',$this->instance->getFileContents('testfile'));
	}

	function testGettingANonexistantFileDoesntWork()
	{
		$this->expectException();
		$this->assertTrue($this->instance->getFileContents('testfile'));
	}

	function testDeletingAFileWorks()
	{
		$this->assertFalse($this->instance->fileExists('testfile'));
		$this->instance->setFileContents('testfile','mmmmm plastic');
		$this->assertTrue($this->instance->fileExists('testfile'));

		$this->instance->deleteFile('testfile');

		$this->expectException();
		$this->instance->openFile('testfile');
	}

	function testOpeningFileWorks()
	{
		$this->assertFalse($this->instance->fileExists('testfile'));
		$this->instance->setFileContents('testfile','mmmmm plastic');
		$this->assertTrue($this->instance->fileExists('testfile'));

		$this->assertTrue(is_resource($this->instance->openFile('testfile')));
	}
}

