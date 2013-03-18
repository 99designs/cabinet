<?php

namespace Cabinet;

class ArrayFileStoreTest
    extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->array = array();

        // create an instance
        $this->instance = new ArrayFileStore($this->array);
        $this->assertTrue(is_object($this->instance));
    }

    public function testFileCreation()
    {
        $this->assertFalse($this->instance->fileExists('testfile'));
        $filehandle = $this->instance->newFile('testfile');
        $this->assertTrue(is_resource($filehandle));

        // write some stuff
        fwrite($filehandle,"this is a test");
        $this->instance->close($filehandle);

        $this->assertTrue($this->instance->fileExists('testfile'));
    }

    public function testFileContentsHelpers()
    {
        $this->assertFalse($this->instance->fileExists('testfile'));
        $this->instance->setFileContents('testfile','mmmmm plastic');
        $this->assertTrue($this->instance->fileExists('testfile'));
        $this->assertEquals('mmmmm plastic',$this->instance->getFileContents('testfile'));
    }

    public function testPuttingTwiceWorksOk()
    {
        $this->assertFalse($this->instance->fileExists('testfile'));
        $this->instance->setFileContents('testfile','mmmmm plastic');
        $this->instance->setFileContents('testfile','frogs are green');
        $this->assertTrue($this->instance->fileExists('testfile'));

        $this->assertEquals('frogs are green',$this->instance->getFileContents('testfile'));
    }

    public function testGettingANonexistantFileDoesntWork()
    {
        $this->setExpectedException('\Cabinet\FileStoreException');
        $this->instance->getFileContents('testfile');
    }

    public function testDeletingAFileWorks()
    {
        $this->assertFalse($this->instance->fileExists('testfile'));
        $this->instance->setFileContents('testfile','mmmmm plastic');
        $this->assertTrue($this->instance->fileExists('testfile'));

        $this->instance->deleteFile('testfile');

        $this->setExpectedException('\Cabinet\FileStoreException');
        $this->instance->openFile('testfile');
    }

    public function testOpeningFileWorks()
    {
        $this->assertFalse($this->instance->fileExists('testfile'));
        $this->instance->setFileContents('testfile','mmmmm plastic');
        $this->assertTrue($this->instance->fileExists('testfile'));

        $this->assertTrue(is_resource($this->instance->openFile('testfile')));
    }
}
