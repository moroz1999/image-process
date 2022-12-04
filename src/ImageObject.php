<?php

namespace ImageProcess;

class ImageObject
{
    protected $originalType;
    protected $width;
    protected $height;
    protected $imageFilePath;
    protected $objectName;
    protected $GDResource;
    protected $cacheString;
    protected $originalSize;
    protected $originalDate;

    public function __construct($objectName, $imageFilePath = "")
    {
        $this->objectName = $objectName;
        $this->imageFilePath = $imageFilePath;
    }

    protected function createEmptyGDResource()
    {
        if ($this->width && $this->height) {
            $this->GDResource = imagecreatetruecolor($this->width, $this->height);

            imagealphablending($this->GDResource, false);
            imagesavealpha($this->GDResource, true);
        }
    }

    /**
     * @return string
     */
    public function getImageFilePath()
    {
        return $this->imageFilePath;
    }

    protected function importImageFile()
    {
        if (is_file($this->imageFilePath)) {
            if ($size = getimagesize($this->imageFilePath)) {
                $this->width = $size[0];
                $this->height = $size[1];

                switch ($size['mime']) {
                    case 'image/jpeg':
                        $this->originalType = 'jpg';
                        $this->GDResource = imagecreatefromjpeg($this->imageFilePath);
                        break;
                    case 'image/gif':
                        $this->originalType = 'gif';
                        $this->GDResource = imagecreatefromgif($this->imageFilePath);
                        break;
                    case 'image/png':
                        $this->originalType = 'png';
                        $this->GDResource = imagecreatefrompng($this->imageFilePath);
                        break;
                    case 'image/bmp':
                        $this->originalType = 'bmp';
                        $this->GDResource = imagecreatefrombmp($this->imageFilePath);
                        break;
                    case 'image/webp':
                        $this->originalType = 'webp';
                        $this->GDResource = imagecreatefromwebp($this->imageFilePath);
                        break;
                }
            }
        }
    }

    /**
     * @return mixed
     */
    public function getGDResource()
    {
        if ($this->GDResource === null) {
            if ($this->imageFilePath != "") {
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
        if ($this->originalType === null) {
            $this->importImageFile();
        }
        return $this->originalType;
    }

    /**
     * @return int
     */
    public function getOriginalSize()
    {
        if ($this->originalSize === null) {
            $this->originalSize = filesize($this->imageFilePath);
        }

        return $this->originalSize;
    }

    /**
     * @return bool|int
     */
    public function getOriginalDate()
    {
        if ($this->originalDate === null) {
            $this->originalDate = filemtime($this->imageFilePath);
        }
        return $this->originalDate;
    }
}