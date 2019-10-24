<?php
namespace S3Stub;

class Client
{
    protected $accessKeyId;

    protected $secretAccessKey;

    public function __construct($accessKeyId, $secretAccessKey)
    {
        $this->accessKeyId = $accessKeyId;
        $this->secretAccessKey = $secretAccessKey;
    }

    /**
     * "Sends" a file to "S3".
     * 
     * @param  string|\SplFileInfo $file Either SplFileInfo or path to a file that will be sent.
     * @param  string $bucketName File to be uploaded.
     * @return FileObject File object.
     */
    public function send($file, $bucketName)
    {
        $file = $file instanceof \SplFileInfo ? $file : new \SplFileInfo($file);

        // make sure valid auth
        if (empty($this->accessKeyId) || empty($this->secretAccessKey)) {
            throw new \RuntimeException('Not authed properly.');
        }
        
        return new FileObject('http://'. $bucketName .'.s3.amazonaws.com/'. $file->getFilename());
    }
}