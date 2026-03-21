<?php

/**
 * This interface specifies the api for uploading files.
 *
 * @package   vvalempoule
 * @author    Jaap van Boxtel
 * @copyright 25-05-2014
 * @version   0.1
 */
 interface iUpload
 {
    public function __construct($fdir, $maxKb, $extentions);
    public function loadUp($file, $map='');
    public function deleteFile($file, $map='');
    public function deleteDir($dir, $skip=false);
 }

?>