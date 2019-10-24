<?php
namespace FTPStub;

use SplFileInfo;

class FTPUploader
{

    /**
     * "Uploads" a file to an FTP.
     * 
     * @param  SplFileInfo $file        File to be uploaded.
     * @param  string      $hostname    
     * @param  string      $username    
     * @param  string      $password    
     * @param  string      $destination Destination dir.
     * @return bool True on success.
     */
    public function uploadFile(SplFileInfo $file, $hostname, $username, $password, $destination = '/')
    {
        // validate arguments
        if (empty($hostname) || empty($username) || empty($password)) {
            throw new \InvalidArgumentException('Not enough arguments');
        }

        // mock auth
        if ($password !== 'encoder') {
            throw new \InvalidArgumentException('Invalid password.');
        }

        return true;
    }

}