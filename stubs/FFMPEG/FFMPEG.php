<?php
namespace FFMPEGStub;

use SplFileInfo;

class FFMPEG
{
    /**
     * "Coverts" a file to MP4.
     * 
     * @param  SplFileInfo $file File to be converted.
     * @return SplFileInfo Converted file.
     */
    public function convert(SplFileInfo $file)
    {
        // just return a converted filename
        return new SplFileInfo('/tmp/'. str_replace('.', '_', $file->getFilename()) .'.encoded.mp4');
    }
}