<?php

namespace Cabinet;

class FilesystemFileStoreSystemTest
    extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        // create us a temporary directory
        $this->tempdir = tempnam('/tmp',__CLASS__);
        unlink($this->tempdir);
        mkdir($this->tempdir);

        // create an instance
        $this->instance = new FilesystemFileStore($this->tempdir);
        $this->assertTrue(is_object($this->instance));
    }

    public function tearDown()
    {
        // remove our temporary directory
        FileHelper::deleteDirectory($this->tempdir);
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
        $this->assertFalse($this->instance->fileExists('testfile'));
    }

    public function testOpeningFileWorks()
    {
        $this->assertFalse($this->instance->fileExists('testfile'));
        $this->instance->setFileContents('testfile','mmmmm plastic');
        $this->assertTrue($this->instance->fileExists('testfile'));

        $this->assertTrue(is_resource($this->instance->openFile('testfile')));
    }

    public function testSetContentsWithAResource()
    {
        $temp = tempnam($this->tempdir, 'meh');
        file_put_contents($temp, 'a resource in time saves nine');
        $fh = fopen($temp, 'r');
        $this->assertTrue(is_resource($fh));

        $filestore = new FilesystemFileStore($this->tempdir);
        $filestore->setFileContents('foo', $fh);
        $this->assertEquals($filestore->getFileContents('foo'),'a resource in time saves nine');
    }

    public function testKeysWithSlashesWork()
    {
        $this->assertFalse($this->instance->fileExists('mydir/testfile'));
        $this->instance->setFileContents('mydir/testfile','mmmmm plastic');
        $this->assertTrue($this->instance->fileExists('mydir/testfile'));
        $this->assertEquals('mmmmm plastic',$this->instance->getFileContents('mydir/testfile'));
    }
}
