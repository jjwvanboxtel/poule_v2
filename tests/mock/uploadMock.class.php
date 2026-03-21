<?php

/**
 * This class is a mock for uploading files.
 *
 * @package   vvalempoule
 * @author    Jaap van Boxtel
 * @copyright 19-01-2014
 * @version   0.1
 */
class UploadMock implements iUpload
{
    /**
     * Constructor for a upload object.
     */
    public function __construct($fdir, $maxKb, $extentions)
    {
    } // __construct()

    /**
     * The main upload method
     *
     * @param file $file
     * @param Sting $map (dir)
     */
    public function loadUp($file, $map='')
    {
        return true;
    } // loadUp

    /**
     * Deletes a file.
     *
     * @param file $file
     * @param String $map (dir)
     * @return boolean true if file is deleted.
     */
    public function deleteFile($file, $map='')
    {
        return true;
    } // deleteFile

    /**
     * Deletes a directory.
     *
     * @param dir $dir
     * @return boolean true if dir is deleted.
     */
    public function deleteDir($dir, $skip=false) {
        if (is_dir($dir))
        {
            $dirs = glob($dir . '/*');
            foreach($dirs as $file) {
                if(is_dir($file))
                    $this->deleteDir($file);
                else
                    unlink($file);
            }
            if (!$skip)
                rmdir($dir);
        }
    }
    
} // Upload
?>
