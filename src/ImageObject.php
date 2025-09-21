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

    /**
     * @throws SourceFileException
     */
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
     * @throws SourceFileException
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

    /**
     * @throws SourceFileException
     */
    protected function importImageFile(): void
    {
        // Check for existance
        if (!is_file($this->imageFilePath)) {
            throw new SourceFileException("File is missing: {$this->imageFilePath}");
        }

        // Detect image info
        $imageInfo = getimagesize($this->imageFilePath);
        if ($imageInfo === false || empty($imageInfo['mime'])) {
            throw new SourceFileException("Unknown image format: {$this->imageFilePath}");
        }

        $mimeType = $imageInfo['mime'];

        // MIME to loader map
        $mimeToLoader = [
            'image/jpeg' => ['ext' => 'jpg', 'loader' => 'imagecreatefromjpeg'],
            'image/gif' => ['ext' => 'gif', 'loader' => 'imagecreatefromgif'],
            'image/png' => ['ext' => 'png', 'loader' => 'imagecreatefrompng'],
            'image/bmp' => ['ext' => 'bmp', 'loader' => 'imagecreatefrombmp'],
            'image/webp' => ['ext' => 'webp', 'loader' => 'imagecreatefromwebp'],
        ];

        if (!isset($mimeToLoader[$mimeType])) {
            throw new SourceFileException("Unsupported image MIME type: {$mimeType} {$this->imageFilePath}");
        }

        $this->originalType = $mimeToLoader[$mimeType]['ext'];
        $loader = $mimeToLoader[$mimeType]['loader'];

        /** @var resource|\GdImage|false $gdImage */
        $gdImage = @$loader($this->imageFilePath);

        if ($gdImage === false) {
            throw new SourceFileException("Failed to create GD image from file: {$this->imageFilePath}");
        }

        $this->GDResource = $gdImage;
        $this->width = imagesx($this->GDResource);
        $this->height = imagesy($this->GDResource);
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
     * @throws SourceFileException
     * @throws SourceFileException
     */
    public function getOriginalType(): ?string
    {
        if ($this->originalType === null) {
            $this->importImageFile();
        }
        return $this->originalType;
    }
}