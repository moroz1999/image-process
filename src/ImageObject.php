<?php

namespace ImageProcess;

class ImageObject
{
    public $originalType = "";
    public $width = "";
    public $height = "";
    public $imageFileName = "";
    public $objectName = "";
    public $GDResource;
    public $cacheString = "";
    public $originalSize;
    public $originalDate;

    function __construct($objectName, $imageFileName = "")
    {
        $this->objectName = $objectName;
        $this->imageFileName = $imageFileName;

        $this->originalDate = filemtime($imageFileName);
        $this->originalSize = filesize($imageFileName);
        $this->cacheString .= $this->originalDate . ' ' . $this->originalSize . ' ';
        $this->prepareGDResource();
    }

    function prepareGDResource($width = null, $height = null)
    {
        if (is_null($this->GDResource)) {
            if ($this->imageFileName != "") {
                $this->importImageFile();
            } else {
                $this->createEmptyGDResource($width, $height);
            }
        }
    }

    function createEmptyGDResource($width, $height)
    {
        if ($width && $height) {
            $this->GDResource = imagecreatetruecolor($width, $height);
            $this->width = $width;
            $this->height = $height;

            imagealphablending($this->GDResource, false);
            imagesavealpha($this->GDResource, true);
        }

    }

    function importImageFile()
    {
        $imageFileName = $this->imageFileName;
        if (is_file($imageFileName)) {
            $size = getimagesize($imageFileName);
            $this->width = $size[0];
            $this->height = $size[1];

            switch ($size['mime']) {
                case 'image/jpeg':
                    $this->originalType = 'jpg';
                    $this->GDResource = imagecreatefromjpeg($imageFileName);
                    break;
                case 'image/gif':
                    $this->originalType = 'gif';
                    $this->GDResource = imagecreatefromgif($imageFileName);
                    break;
                case 'image/png':
                    $this->originalType = 'png';
                    $this->GDResource = imagecreatefrompng($imageFileName);
                    break;
                case 'image/bmp':
                    $this->originalType = 'bmp';
                    if (!function_exists('imagecreatefrombmp')) {
                        include_once('function.imagecreatefrombmp.php');
                    }
                    $this->GDResource = imagecreatefrombmp($imageFileName);
                    break;
            }
        }
    }

    function updateStatus()
    {
        $this->width = imagesx($this->GDResource);
        $this->height = imagesy($this->GDResource);
    }
}