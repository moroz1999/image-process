<?php
declare(strict_types=1);

namespace ImageProcess;

use GdImage;

class ImageObject
{
    protected string $objectName;
    protected ?string $imageFilePath;
    protected ?string $originalType = null;
    protected ?int $width = null;
    protected ?int $height = null;
    protected ?GdImage $GDResource = null;
    protected ?string $cacheString = null;
    protected ?int $originalSize = null;
    protected ?int $originalDate = null;

    public function __construct(string $objectName, ?string $imageFilePath = null)
    {
        $this->objectName = $objectName;
        $this->imageFilePath = $imageFilePath;
    }

    /**
     * @return string
     */
    public function getImageFilePath(): string
    {
        return $this->imageFilePath;
    }

    public function getWidth(): int
    {
        if ($this->width === null) {
            $this->width = imagesx($this->getGDResource());
        }

        return $this->width;
    }

    public function setWidth(int $width): void
    {
        $this->width = $width;
    }

    /**
     * @return mixed
     */
    public function getGDResource(): GdImage
    {
        if ($this->GDResource === null) {
            if ($this->imageFilePath !== null) {
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
    public function setGDResource(GdImage $GDResource): void
    {
        $this->GDResource = $GDResource;
        $this->width = null;
        $this->height = null;
    }

    protected function importImageFile(): void
    {
        if (is_file($this->imageFilePath)) {
            if ($info = getimagesize($this->imageFilePath)) {
                switch ($info['mime']) {
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
                if ($this->GDResource) {
                    $this->width = imagesx($this->GDResource);
                    $this->height = imagesy($this->GDResource);
                }
            }
        }
    }

    protected function createEmptyGDResource(): void
    {
        if ($this->width && $this->height) {
            $this->GDResource = imagecreatetruecolor($this->width, $this->height);

            imagealphablending($this->GDResource, false);
            imagesavealpha($this->GDResource, true);
        }
    }

    public function getHeight(): int
    {
        if ($this->height === null) {
            $this->height = imagesy($this->getGDResource());
        }

        return $this->height;
    }

    public function setHeight(int $height): void
    {
        $this->height = $height;
    }

    /**
     * @return string
     */
    public function getCacheString(): string
    {
        if ($this->cacheString === null) {
            $this->cacheString .= $this->getOriginalDate() . ' ' . $this->getOriginalSize() . ' ';
        }
        return $this->cacheString;
    }

    /**
     * @return bool|int|null
     */
    public function getOriginalDate(): bool|int|null
    {
        if ($this->originalDate === null) {
            $this->originalDate = filemtime($this->imageFilePath);
        }
        return $this->originalDate;
    }

    /**
     * @return int|null
     */
    public function getOriginalSize(): ?int
    {
        if ($this->originalSize === null) {
            $this->originalSize = filesize($this->imageFilePath);
        }

        return $this->originalSize;
    }

    /**
     * @param string $value
     */
    public function appendCacheString($value): void
    {
        $this->cacheString .= $value;
    }

    /**
     * @return string|null
     */
    public function getOriginalType(): ?string
    {
        if ($this->originalType === null) {
            $this->importImageFile();
        }
        return $this->originalType;
    }
}