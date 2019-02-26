<?php

namespace ImageProcess;

class ImageProcess
{
    /**
     * @var Filter[]
     */
    protected $filters = [];
    /**
     * @var ImageObject[]
     */
    protected $images = [];
    /**
     * @var string[][]
     */
    protected $exportList = [];
    /**
     * @var string
     */
    protected $imageProcessPath;
    /**
     * @var string
     */
    protected $cachePath;
    /**
     * @var string
     */
    protected $cacheDirMarkerPath;
    /**
     * @var string
     */
    protected $processFiltersPath;
    protected $quality = 90;
    protected $imagesCaching = true;
    protected $defaultCachePermissions = 0777;

    /**
     * ImageProcess constructor.
     * @param string $cachePath
     */
    public function __construct($cachePath = '')
    {
        $this->imageProcessPath = dirname(__FILE__);
        $this->processFiltersPath = $this->imageProcessPath . '/process_filters/';

        $this->setCachePath($cachePath);
    }

    /**
     * @param bool $imagesCaching
     */
    public function setImagesCaching($imagesCaching)
    {
        $this->imagesCaching = $imagesCaching;
    }

    /**
     * @param string $path
     */
    public function setCachePath($path)
    {
        $this->cachePath = $path;
        $this->cacheDirMarkerPath = $this->cachePath . '/_marker';
        $this->checkCachePath();
    }

    /**
     * @param int $width
     * @param int $height
     * @return ImageObject
     */
    public function getEmptyImageObject($width, $height)
    {
        $newObject = new ImageObject('', '');
        $newObject->setWidth($width);
        $newObject->setHeight($height);
        return $newObject;
    }

    /**
     * @param ImageObject $imageObject
     * @return ImageObject
     */
    public function getImageObjectCopy($imageObject)
    {
        $newObject = clone($imageObject);

        return $newObject;
    }

    /**
     * @param string $filterName
     * @param string $parameters
     * @param string $outgoingObjectName
     * @param string $incomingObjectName
     * @param string $incomingObject2Name
     */
    public function registerFilter(
        $filterName,
        $parameters = "",
        $outgoingObjectName = "",
        $incomingObjectName = "",
        $incomingObject2Name = ""
    ) {
        if ($this->images) {
            if ($incomingObjectName == "") {
                $incomingObject = reset($this->images);
            } else {
                $incomingObject = $this->images[$incomingObjectName];
            }

            if ($outgoingObjectName == '') {
                $outgoingObject = $incomingObject;
            } else {
                if (isset($this->images[$outgoingObjectName]) && is_object($this->images[$outgoingObjectName])) {
                    $outgoingObject = $this->images[$outgoingObjectName];
                } else {
                    $this->images[$outgoingObjectName] = new ImageObject($outgoingObjectName);

                    $outgoingObject = $this->images[$outgoingObjectName];
                }
            }

            if ($incomingObject2Name == '') {
                $incomingObject2 = $incomingObject;
            } else {
                if (isset($this->images[$incomingObject2Name]) && is_object($this->images[$incomingObject2Name])) {
                    $incomingObject2 = $this->images[$incomingObject2Name];
                } else {
                    $this->images[$incomingObject2Name] = new ImageObject($incomingObject2Name);

                    $incomingObject2 = $this->images[$incomingObject2Name];
                }
            }

            $filterClassFileName = $this->processFiltersPath . ucfirst($filterName) . ".php";
            if (file_exists($filterClassFileName)) {
                require_once $filterClassFileName;
            }
            $filterClassName = '\ImageProcess\\' . ucfirst($filterName);

            $filterObject = new $filterClassName($filterClassName, $parameters);
            $filterObject->incomingObject = $incomingObject;
            $filterObject->incomingObject2 = $incomingObject2;
            $filterObject->outgoingObject = $outgoingObject;

            $this->filters[] = $filterObject;

            $outgoingObject->appendCacheString($filterClassName . ' ' . $incomingObject->getCacheString() . $incomingObject2->getCacheString() . ' ' . $parameters . ' ');
        }
    }

    /**
     * @param string $objectName
     * @param string $imageFileName
     * @return string
     */
    public function registerImage($objectName = "", $imageFileName = "")
    {
        if ($objectName == "") {
            $objectsCount = count($this->images);
            $objectName = "imageObject_" . $objectsCount;
        }

        $this->images[$objectName] = new ImageObject($objectName, $imageFileName);

        return $objectName;
    }

    public function executeProcess()
    {
        $imagesCached = true;
        foreach ($this->exportList as $key => &$exportOperation) {
            if ($exportOperation['cacheExists'] == false) {
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
     * @param string $objectName
     * @param string $fileType
     * @param string $fileName
     * @param int $quality
     * @param bool $interlace
     * @param string $cacheFileName
     * @param string $cacheGroup
     * @return string[]
     */
    public function registerExport(
        $objectName = null,
        $fileType = null,
        $fileName = "",
        $quality = null,
        $interlace = false,
        $cacheFileName = '',
        $cacheGroup = ''
    ) {
        if (is_null($quality)) {
            $quality = $this->quality;
        }

        $exportOperation = [];
        if (is_null($objectName)) {
            foreach ($this->images as $key => $imageObject) {
                $objectName = &$key;
                break;
            }
        }
        if (is_null($fileType)) {
            $fileType = $this->images[$objectName]->getOriginalType();
        }

        $exportOperation['objectName'] = $objectName;
        $exportOperation['fileType'] = $fileType;
        $exportOperation['fileName'] = $fileName;
        $exportOperation['quality'] = $quality;
        $exportOperation['interlace'] = $interlace;

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

    protected function exportImage($exportOperation)
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
        $interlace = $exportOperation['interlace'];
        $cacheFilePath = $exportOperation['cacheFilePath'];

        if (!$cacheExists || !$this->imagesCaching) {
            if ($cacheGroup) {
                if (!is_dir($this->cachePath . $cacheFileName)) {
                    mkdir($this->cachePath . $cacheFileName, $this->defaultCachePermissions, true);
                }
            }
            if (is_object($this->images[$objectName])) {
                $imageObject = $this->images[$objectName];
                $temporaryGDResource = imagecreatetruecolor($imageObject->getWidth(), $imageObject->getHeight());
                if ($fileType == 'png') {
                    imagealphablending($temporaryGDResource, false);
                    imagesavealpha($temporaryGDResource, true);
                }
                imagecopyresampled($temporaryGDResource, $imageObject->getGDResource(), 0, 0, 0, 0,
                    $imageObject->getWidth(), $imageObject->getHeight(), $imageObject->getWidth(),
                    $imageObject->getHeight());

                if ($interlace) {
                    imageinterlace($temporaryGDResource, true);
                }
                switch ($fileType) {
                    case 'jpg':
                    case 'jpeg':
                        imagejpeg($temporaryGDResource, $cacheFilePath, $quality);
                        break;
                    case 'png':
                        imagepng($temporaryGDResource, $cacheFilePath);
                        break;

                    case 'gif':
                        imagegif($temporaryGDResource, $cacheFilePath);
                        break;

                    case 'bmp':
                        imagebmp($temporaryGDResource, $cacheFilePath);
                        break;

                    case 'webp':
                        imagewebp($temporaryGDResource, $cacheFilePath, $quality);
                        break;
                }
                chmod($cacheFilePath, $this->defaultCachePermissions);
            }
        }

        if ($fileName != '') {
            $fileContents = file_get_contents($cacheFilePath);
            file_put_contents($fileName, $fileContents);
        }
    }

    protected function checkCachePath()
    {
        if ($this->cachePath && !is_dir($this->cachePath)) {
            mkdir($this->cachePath, $this->defaultCachePermissions, true);
        }
    }

    public function setDefaultCachePermissions($defaultCachePermissions)
    {
        $this->defaultCachePermissions = $defaultCachePermissions;
    }
}
