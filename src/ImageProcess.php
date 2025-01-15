<?php
declare(strict_types=1);

namespace ImageProcess;

use Imagick;
use ImagickException;
use ImagickPixel;
use RuntimeException;

class ImageProcess
{
    /**
     * @var Filter[]
     */
    protected array $filters = [];
    /**
     * @var array<string, ImageObject>
     */
    protected array $images = [];
    /**
     * @var string[][]
     */
    protected array $exportList = [];
    protected string $cachePath;
    protected string $cacheDirMarkerPath;
    protected int $quality = 90;
    protected bool $imagesCaching = true;
    protected int $defaultCachePermissions = 0777;
    protected bool $gammaCorrectionEnabled = false;

    public function __construct(string $cachePath = '')
    {
        $this->setCachePath($cachePath);
    }

    public function setCachePath(string $path): void
    {
        $this->cachePath = $path;
        $this->cacheDirMarkerPath = $this->cachePath . '/_marker';
        $this->checkCachePath();
    }

    protected function checkCachePath(): void
    {
        if ($this->cachePath && !is_dir($this->cachePath)) {
            if (!mkdir($concurrentDirectory = $this->cachePath, $this->defaultCachePermissions, true) && !is_dir($concurrentDirectory)) {
                throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
        }
    }

    public function setGammaCorrectionEnabled(bool $gammaCorrectionEnabled): void
    {
        $this->gammaCorrectionEnabled = $gammaCorrectionEnabled;
    }

    public function setImagesCaching(bool $imagesCaching): void
    {
        $this->imagesCaching = $imagesCaching;
    }

    public function getEmptyImageObject(int $width, int $height): ImageObject
    {
        $newObject = new ImageObject('');
        $newObject->setWidth($width);
        $newObject->setHeight($height);
        return $newObject;
    }

    public function getImageObjectCopy(ImageObject $imageObject): ImageObject
    {
        return clone($imageObject);
    }

    public function registerFilter(
        string  $filterName,
        ?array  $parameters = null,
        ?string $outgoingObjectName = null,
        ?string $incomingObjectName = null,
        ?string $incomingObject2Name = null,
    ): void
    {
        $incomingObjectName ??= "";
        $outgoingObjectName ??= "";
        $incomingObject2Name ??= "";
        $parameters ??= "";
        if ($this->images) {
            if ($incomingObjectName === "") {
                $incomingObject = reset($this->images);
            } else {
                $incomingObject = $this->images[$incomingObjectName];
            }

            if ($outgoingObjectName === '') {
                $outgoingObject = $incomingObject;
            } else {
                if (isset($this->images[$outgoingObjectName]) && is_object($this->images[$outgoingObjectName])) {
                    $outgoingObject = $this->images[$outgoingObjectName];
                } else {
                    $this->images[$outgoingObjectName] = new ImageObject($outgoingObjectName);

                    $outgoingObject = $this->images[$outgoingObjectName];
                }
            }

            if ($incomingObject2Name === '') {
                $incomingObject2 = $incomingObject;
            } else {
                if (isset($this->images[$incomingObject2Name]) && is_object($this->images[$incomingObject2Name])) {
                    $incomingObject2 = $this->images[$incomingObject2Name];
                } else {
                    $this->images[$incomingObject2Name] = new ImageObject($incomingObject2Name);

                    $incomingObject2 = $this->images[$incomingObject2Name];
                }
            }

            $filterClassName = '\\ImageProcess\\Filters\\' . ucfirst($filterName);

            $filterObject = new $filterClassName($filterClassName, $parameters);
            $filterObject->incomingObject = $incomingObject;
            $filterObject->incomingObject2 = $incomingObject2;
            $filterObject->outgoingObject = $outgoingObject;

            $this->filters[] = $filterObject;

            $outgoingObject->appendCacheString($filterClassName . ' ' . $incomingObject->getCacheString() . $incomingObject2->getCacheString() . ' ' . json_encode($parameters) . ' ');
        }
    }

    public function registerImage(?string $objectName = null, ?string $imageFileName = null): string
    {
        if ($objectName === null) {
            $objectsCount = count($this->images);
            $objectName = "imageObject_" . $objectsCount;
        }

        if ($imageFileName !== null && !is_file($imageFileName)) {
            throw new RuntimeException(sprintf('File "%s" does not exist', $imageFileName));
        }

        $this->images[$objectName] = new ImageObject($objectName, $imageFileName);

        return $objectName;
    }

    public function executeProcess(): void
    {
        if ($this->gammaCorrectionEnabled) {
            foreach ($this->images as $imageObject) {
                if ($resource = $imageObject->getGDResource()) {
                    imagegammacorrect($resource, 2.2, 1.0);
                    $imageObject->setGDResource($resource);
                }
            }
        }

        $imagesCached = true;
        foreach ($this->exportList as $exportOperation) {
            if ($exportOperation['cacheExists'] === false) {
                $imagesCached = false;
                break;
            }
        }

        if (!$imagesCached || !$this->imagesCaching) {
            if ($this->filters) {
                foreach ($this->filters as $filter) {
                    $filter->startProcess($this);
                }
            }
        }

        foreach ($this->exportList as $exportOperation) {
            $this->exportImage($exportOperation);
        }
    }

    /**
     * @throws ImagickException
     */
    protected function exportImage($exportOperation): void
    {
        $objectName = $exportOperation['objectName'];
        $cacheExists = $exportOperation['cacheExists'];
        $fileType = $exportOperation['fileType'];
        $fileName = $exportOperation['fileName'];
        $cacheFileName = $exportOperation['cacheFileName'];
        $cacheGroup = $exportOperation['cacheGroup'];
        if (!empty($exportOperation['jpegQuality'])) {
            $quality = $exportOperation['jpegQuality'];
        } else {
            $quality = $exportOperation['quality'];
        }
        $lossless = $exportOperation['lossless'];
        $cacheFilePath = $exportOperation['cacheFilePath'];

        if (!$cacheExists || !$this->imagesCaching) {
            if ($cacheGroup) {
                if (!is_dir($this->cachePath . $cacheFileName)) {
                    if (!mkdir($concurrentDirectory = $this->cachePath . $cacheFileName, $this->defaultCachePermissions, true) && !is_dir($concurrentDirectory)) {
                        throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
                    }
                }
            }
            if (is_object($this->images[$objectName]) && $this->images[$objectName]->getGDResource()) {
                $imageObject = $this->images[$objectName];
                if ($fileType === 'svg') {
                    copy($imageObject->getImageFilePath(), $cacheFilePath);
                } else {
                    $temporaryGDResource = imagecreatetruecolor($imageObject->getWidth(), $imageObject->getHeight());
                    if ($fileType === 'png' || $fileType === 'webp') {
                        imagealphablending($temporaryGDResource, false);
                        imagesavealpha($temporaryGDResource, true);
                    }
                    if ($this->gammaCorrectionEnabled) {
                        imagegammacorrect($imageObject->getGDResource(), 1.0, 2.2);
                    }
                    imagecopyresampled($temporaryGDResource, $imageObject->getGDResource(), 0, 0, 0, 0,
                        $imageObject->getWidth(), $imageObject->getHeight(), $imageObject->getWidth(),
                        $imageObject->getHeight());


                    if ($fp = fopen($cacheFilePath, 'wb')) {
                        if (flock($fp, LOCK_EX)) {
                            $gdCacheFile = $cacheFilePath . 'gd';
                            switch ($fileType) {
                                case 'jpg':
                                case 'jpeg':
                                    imagejpeg($temporaryGDResource, $gdCacheFile, $quality);
                                    break;

                                case 'png':
                                    imagepng($temporaryGDResource, $gdCacheFile);
                                    break;

                                case 'gif':
                                    imagegif($temporaryGDResource, $gdCacheFile);
                                    break;

                                case 'bmp':
                                    imagebmp($temporaryGDResource, $gdCacheFile);
                                    break;

                                case 'webp':
                                    if (class_exists('Imagick')) {
                                        imagepng($temporaryGDResource, $gdCacheFile);

                                        $image = new Imagick();
                                        $image->pingImage($gdCacheFile);
                                        $image->readImage($gdCacheFile);
                                        $image->setImageFormat("webp");
                                        $image->setOption('webp:method', '6');
                                        if (!$lossless) {
                                            $image->setImageCompressionQuality($quality);
                                        } else {
                                            $image->setOption('webp:lossless', 'true');
                                            $image->setImageAlphaChannel(Imagick::ALPHACHANNEL_ACTIVATE);
                                            $image->setBackgroundColor(new ImagickPixel('transparent'));
                                        }
                                        $image->writeImage($gdCacheFile);
                                    } else {
                                        imagewebp($temporaryGDResource, $gdCacheFile, $quality);
                                    }
                                    break;
                            }
                            ftruncate($fp, 0);
                            fwrite($fp, file_get_contents($gdCacheFile));
                            unlink($gdCacheFile);
                            flock($fp, LOCK_UN);
                        }
                        fclose($fp);
                    }
                }
                chmod($cacheFilePath, $this->defaultCachePermissions);
            }
        }

        if ($fileName !== null) {
            $fileContents = file_get_contents($cacheFilePath);
            file_put_contents($fileName, $fileContents);
        }
    }

    /**
     * @return string[]
     */
    public function registerExport(
        ?string $objectName = null,
        ?string $fileType = null,
        ?string $fileName = null,
        ?int    $quality = null,
        bool    $lossless = false,
        string  $cacheFileName = '',
        string  $cacheGroup = '',
    ): array
    {
        if ($quality === null) {
            $quality = $this->quality;
        }

        $exportOperation = [];
        if ($objectName === null) {
            $objectName = array_key_first($this->images);
        }
        if ($fileType === null) {
            $fileType = $this->images[$objectName]->getOriginalType();
        }

        $exportOperation['objectName'] = $objectName;
        $exportOperation['fileType'] = $fileType;
        $exportOperation['fileName'] = $fileName;
        $exportOperation['quality'] = $quality;
        $exportOperation['lossless'] = $lossless;

        $imageObject = $this->images[$exportOperation['objectName']];

        if (!empty($quality)) {
            $exportOperation['parametersHash'] = md5($imageObject->getCacheString() . ' ' . $exportOperation['fileType'] . ' ' . $quality);
        } else {
            $exportOperation['parametersHash'] = md5($imageObject->getCacheString() . ' ' . $exportOperation['fileType']);
        }

        if (!$cacheFileName) {
            $cacheFileName = $exportOperation['parametersHash'];
        }

        if ($cacheGroup) {
            $cacheFilePath = $this->cachePath . $cacheFileName . '/' . $cacheGroup;
        } else {
            $cacheFilePath = $this->cachePath . $cacheFileName;
        }

        $exportOperation['cacheFileName'] = $cacheFileName;
        $exportOperation['cacheGroup'] = $cacheGroup;
        $exportOperation['cacheFilePath'] = $cacheFilePath;
        $exportOperation['cacheExists'] = is_file($cacheFilePath);

        $this->exportList[] = $exportOperation;
        return $exportOperation;
    }

    public function setDefaultCachePermissions($defaultCachePermissions): void
    {
        $this->defaultCachePermissions = $defaultCachePermissions;
    }
}
