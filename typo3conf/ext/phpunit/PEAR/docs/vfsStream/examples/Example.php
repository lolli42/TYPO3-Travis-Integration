<?php
/**
 * Example class.
 *
 * @package     bovigo_vfs
 * @subpackage  examples
 * @version     $Id: Example.php 79708 2011-06-28 07:50:21Z dkd-webler $
 */
/**
 * Example class.
 *
 * @package     bovigo_vfs
 * @subpackage  examples
 */
class Example
{
    /**
     * id of the example
     *
     * @var  string
     */
    protected $id;
    /**
     * a directory where we do something..
     *
     * @var  string
     */
    protected $directory;

    /**
     * constructor
     *
     * @param  string  $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * sets the directory
     *
     * @param  string  $directory
     */
    public function setDirectory($directory)
    {
        $this->directory = $directory . DIRECTORY_SEPARATOR . $this->id;
        if (file_exists($this->directory) === false) {
            mkdir($this->directory, 0700, true);
        }
    }

    // more source code here...
}
?>