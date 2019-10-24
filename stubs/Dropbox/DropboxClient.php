<?php
namespace DropboxStub;

use SplFileInfo;

class DropboxClient
{
    protected $accessKey;

    protected $secretToken;

    protected $container;

    public function __construct($accessKey, $secretToken, $container)
    {
        $this->accessKey = $accessKey;
        $this->secretToken = $secretToken;
        $this->container = $container;
    }

    /**
     * "Uploads" file to Dropbox.
     * 
     * @param  SplFileInfo $file File to be uploaded.
     * @return string URL to the uploaded file.
     */
    public function upload(SplFileInfo $file)
    {
        return 'http://uploads.dropbox.com/' . $this->container .'/'. $file->getFilename();
    }
}