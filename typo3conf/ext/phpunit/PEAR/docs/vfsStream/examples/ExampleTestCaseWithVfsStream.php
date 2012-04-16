<?php
/**
 * Test case for class Example.
 *
 * @package     bovigo_vfs
 * @subpackage  examples
 * @version     $Id: ExampleTestCaseWithVfsStream.php 79708 2011-06-28 07:50:21Z dkd-webler $
 */
require_once 'PHPUnit/Framework.php';
require_once 'vfsStream/vfsStream.php';
require_once 'Example.php';
/**
 * Test case for class Example.
 *
 * @package     bovigo_vfs
 * @subpackage  examples
 */
class ExampleTestCaseWithVfsStream extends PHPUnit_Framework_TestCase
{
    /**
     * set up test environmemt
     */
    public function setUp()
    {
        vfsStream::setup('exampleDir');
    }

    /**
     * test that the directory is created
     */
    public function testDirectoryIsCreated()
    {
        $example = new Example('id');
        $this->assertFalse(vfsStreamWrapper::getRoot()->hasChild('id'));
        $example->setDirectory(vfsStream::url('exampleDir'));
        $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild('id'));
    }
}
?>