<?php
namespace S3Stub;

class FileObject
{
    protected $publicUrl;

    public function __construct($publicUrl)
    {
        $this->publicUrl = $publicUrl;
    }

    public function getPublicUrl()
    {
        return $this->publicUrl;
    }
}