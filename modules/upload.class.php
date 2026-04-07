<?php

/**
 * This class is for uploading files.
 *
 * @package   vvalempoule
 * @author    Jaap van Boxtel
 * @copyright 19-01-2014
 * @version   0.1
 */
class Upload implements iUpload
{
    private $directory;
    private $maxKb;
    private $ext;

    /**
     * Constructor for a upload object.
     */
    public function __construct($fdir, $maxKb, $extentions)
    {
        // set the constants.
        $this->directory = $fdir;
        $this->maxKb = $maxKb;       
        $this->ext = $extentions;
    } // __construct()

    /**
     *  Uploads a file using a cryptographically random, safe filename.
     *
     * @param array  $file  The $_FILES entry for the uploaded file.
     * @param string $map   Optional sub-directory inside the upload root.
     * @return string       The safe filename that was stored on disk.
     */
    private function fileUpload($file, $map='')
    {
        $path = $this->directory.$map;

        if (!file_exists($path)) {
            mkdir($path, 0750, true);
        }

        // Build a random, non-guessable filename that retains the original
        // extension so images/documents are still served correctly, but the
        // random prefix prevents an attacker from knowing or predicting the
        // stored path even if a malicious file passes extension validation.
        // Strip any non-alphanumeric characters from the extension to prevent
        // null-byte, double-extension, or other injection tricks.
        $rawExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $ext    = preg_replace('/[^a-z0-9]/', '', $rawExt);
        $safe   = bin2hex(random_bytes(16)) . ($ext !== '' ? '.' . $ext : '');

        if(is_uploaded_file($file['tmp_name']))
        {
            if(move_uploaded_file($file['tmp_name'], './'.$path.$safe))
                return $safe;
        }

        return false;
    } // fileUpload

    /**
     * The main upload method.
     *
     * @param array  $file  The $_FILES entry for the uploaded file.
     * @param string $map   Optional sub-directory inside the upload root.
     * @return string       The safe filename that was stored on disk.
     * @throws InputException on validation failure or failed move.
     */
    public function loadUp($file, $map='')
    {
        if($this->checkSize($file))
        {
            if($this->checkExt($file))
            {
                $safe = $this->fileUpload($file, $map);
                if($safe === false)
                    throw new InputException('{ERROR_UPLOAD}', 'file');
                return $safe;
            }
            else
            {
                throw new InputException('{ERROR_WRONG_EXTENSION}', 'file');
            }
        }
        else
        {
            throw new InputException('{ERROR_FILE_SIZE}', 'file');
        }
    } // loadUp

    /**
     * Checks if the extension is valid.
     *
     * @param array $file
     * @return bool true if the extension of a file is valid.
     */
    private function checkExt(array $file): bool
    {
        $allowed = array_map('trim', explode(',', $this->ext));
        $rawExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($rawExt === '') {
            return false;
        }
        $ext = '.' . $rawExt;
        return in_array($ext, $allowed, true);
    } // checkExt

    /**
     * Checks of the filesize is valid
     *
     * @param file $file
     * @return boolean true if the size of a file smaller is than the max file size.
     */
    private function checkSize($file)
    {
        $bytes = $this->maxKb*1024;
        $bytes = ceil($bytes);

        if($file['size'] < $bytes)
          return true;
        else
          return false;
    } // checkSize

    /**
     * Deletes a file.
     *
     * @param file $file
     * @param String $map (dir)
     * @return boolean true if file is deleted.
     */
    public function deleteFile($file, $map='')
    {
        $path = trim($this->directory.$map);
        if(file_exists($path.$file))
        {
            if(!unlink($path.$file))
                return false;
        }
		else
		{
			return false;
		}

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
