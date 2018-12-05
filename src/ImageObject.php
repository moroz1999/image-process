<?php

namespace ImageProcess;

class ImageObject
{
    protected $originalType;
    protected $width;
    protected $height;
    protected $imageFileName;
    protected $objectName;
    protected $GDResource;
    protected $cacheString;
    protected $originalSize;
    protected $originalDate;

    public function __construct($objectName, $imageFileName = "")
    {
        $this->objectName = $objectName;
        $this->imageFileName = $imageFileName;
    }

    protected function createEmptyGDResource()
    {
        if ($this->width && $this->height) {
            $this->GDResource = imagecreatetruecolor($this->width, $this->height);

            imagealphablending($this->GDResource, false);
            imagesavealpha($this->GDResource, true);
        }
    }

    protected function importImageFile()
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

    /**
     * @return mixed
     */
    public function getGDResource()
    {
        if ($this->GDResource === null) {
            if ($this->imageFileName != "") {
                $this->importImageFile();
            } else {
                $this->createEmptyGDResource();
            }
        }

        return $this->GDResource;
    }

    /**
     * @param mixed $GDResource
     */
    public function setGDResource($GDResource)
    {
        $this->GDResource = $GDResource;
        $this->width = null;
        $this->height = null;
    }

    /**
     * @return string
     */
    public function getWidth()
    {
        if ($this->width === null) {
            $this->width = imagesx($this->getGDResource());
        }

        return $this->width;
    }

    /**
     * @param string $width
     */
    public function setWidth($width)
    {
        $this->width = (int)$width;
    }

    /**
     * @return string
     */
    public function getHeight()
    {
        if ($this->height === null) {
            $this->height = imagesy($this->getGDResource());
        }

        return $this->height;
    }

    /**
     * @param string $height
     */
    public function setHeight($height)
    {
        $this->height = (int)$height;
    }

    /**
     * @return string
     */
    public function getCacheString()
    {
        if ($this->cacheString === null) {
            $this->cacheString .= $this->getOriginalDate() . ' ' . $this->getOriginalSize() . ' ';
        }
        return $this->cacheString;
    }

    /**
     * @param string $value
     */
    public function appendCacheString($value)
    {
        $this->cacheString .= $value;
    }

    /**
     * @return string
     */
    public function getOriginalType()
    {
        return $this->originalType;
    }

    /**
     * @return int
     */
    public function getOriginalSize()
    {
        if ($this->originalSize === null) {
            $this->originalSize = filesize($this->imageFileName);
        }

        return $this->originalSize;
    }

    /**
     * @return bool|int
     */
    public function getOriginalDate()
    {
        if ($this->originalDate === null) {
            $this->originalDate = filemtime($this->imageFileName);
        }
        return $this->originalDate;
    }
}