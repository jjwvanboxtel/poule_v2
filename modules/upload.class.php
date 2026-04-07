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
     *  Uploads a file.
     *
     * @param file $file
     * @param String $map
     * @return boolean if a file is uploaded with succes.
     */
    private function fileUpload($file, $map='')
    {
        $path = $this->directory.$map;

        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        
        if(is_uploaded_file($file['tmp_name']))
        {
			if(move_uploaded_file($file['tmp_name'], './'.$path.$file['name']))
                return true;
        }

        return false;
    } // fileUpload

    /**
     * The main upload method
     *
     * @param file $file
     * @param Sting $map (dir)
     */
    public function loadUp($file, $map='')
    {
        if($this->checkSize($file))
        {
            if($this->checkExt($file))
            {
				$path = $this->directory.$map;
                if(!file_exists($path.$file['name']))
				{
					if(!$this->fileUpload($file, $map))
					  throw new InputException('{ERROR_UPLOAD}', 'file');
				}
				else
				{
					throw new InputException('{ERROR_F_ALREADY_EXISTS}', 'file');
				}
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
