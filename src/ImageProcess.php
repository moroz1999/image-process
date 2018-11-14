<?php

namespace ImageProcess;

class ImageProcess
{
    protected $filters = array();
    protected $images = array();
    protected $exportList = array();
    protected $imageProcessPath = null;
    protected $cachePath = null;
    protected $cacheDirMarkerPath = null;
    protected $processFiltersPath = null;
    protected $jpegQuality = '90';
    protected $imagesCaching = true;

    /**
     * @param bool $imagesCaching
     */
    public function setImagesCaching($imagesCaching)
    {
        $this->imagesCaching = $imagesCaching;
    }

    protected $defaultCachePermissions = 0777;

    public function __construct($cachePath = '')
    {
        $this->imageProcessPath = dirname(__FILE__);
        $this->processFiltersPath = $this->imageProcessPath . '/process_filters/';

        $this->setCachePath($cachePath);
    }

    public function setCachePath($path)
    {
        $this->cachePath = $path;
        $this->cacheDirMarkerPath = $this->cachePath . '/_marker';
        $this->checkCachePath();
    }

    public function getEmptyImageObject($width, $height)
    {
        $newObject = new ImageObject('', '');
        $newObject->prepareGDResource($width, $height);
        return $newObject;
    }

    public function getImageObjectCopy($imageObject)
    {
        $newObject = clone($imageObject);

        return $newObject;
    }

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

            $outgoingObject->cacheString .= $filterClassName . ' ' . $incomingObject->cacheString . $incomingObject2->cacheString . ' ' . $parameters . ' ';
        }
    }

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
            } else {
                foreach ($this->images as &$imageObject) {
                    $imageObject->prepareGDResource();
                }
            }
        }

        foreach ($this->exportList as $exportOperation) {
            $this->exportImage($exportOperation);
        }
    }

    public function registerExport(
        $objectName = null,
        $fileType = null,
        $fileName = "",
        $jpegQuality = null,
        $interlace = false,
        $cacheFileName = '',
        $cacheGroup = ''
    ) {
        if (is_null($jpegQuality)) {
            $jpegQuality = $this->jpegQuality;
        }

        $exportOperation = array();
        if (is_null($objectName)) {
            foreach ($this->images as $key => $imageObject) {
                $objectName = &$key;
                break;
            }
        }
        if (is_null($fileType)) {
            $fileType = $this->images[$objectName]->originalType;
        }

        $exportOperation['objectName'] = $objectName;
        $exportOperation['fileType'] = $fileType;
        $exportOperation['fileName'] = $fileName;
        $exportOperation['jpegQuality'] = $jpegQuality;
        $exportOperation['interlace'] = $interlace;
        $exportOperation['cacheFileName'] = null;
        $exportOperation['parametersHash'] = null;

        $imageObject = $this->images[$exportOperation['objectName']];

        if ($exportOperation['fileType'] == 'jpg') {
            $exportOperation['parametersHash'] = md5($imageObject->cacheString . ' ' . $exportOperation['fileType'] . ' ' . $jpegQuality);
        } else {
            $exportOperation['parametersHash'] = md5($imageObject->cacheString . ' ' . $exportOperation['fileType']);
        }

        if (!$cacheFileName) {
            $cacheFileName = $exportOperation['parametersHash'];
        }

        $exportOperation['cacheFileName'] = $cacheFileName;
        $exportOperation['cacheGroup'] = $cacheGroup;

        $this->exportList[] = $exportOperation;
        return $exportOperation;
    }

    protected function exportImage($exportOperation)
    {
        $objectName = $exportOperation['objectName'];
        $parametersHash = $exportOperation['parametersHash'];
        $fileType = $exportOperation['fileType'];
        $fileName = $exportOperation['fileName'];
        $cacheFileName = $exportOperation['cacheFileName'];
        $cacheGroup = $exportOperation['cacheGroup'];
        $jpegQuality = $exportOperation['jpegQuality'];
        $interlace = $exportOperation['interlace'];

        if ($cacheGroup) {
            $cacheFilePath = $this->cachePath . $cacheFileName . '/' . $cacheGroup;
        } else {
            $cacheFilePath = $this->cachePath . $cacheFileName;
        }

        if (!file_exists($cacheFilePath) || !$this->imagesCaching) {
            if ($cacheGroup) {
                if (!is_dir($this->cachePath . $cacheFileName)) {
                    mkdir($this->cachePath . $cacheFileName, $this->defaultCachePermissions, true);
                }
            }
            if (is_object($this->images[$objectName])) {
                $imageObject = $this->images[$objectName];
                $temporaryGDResource = imagecreatetruecolor($imageObject->width, $imageObject->height);
                if ($fileType == 'png') {
                    imagealphablending($temporaryGDResource, false);
                    imagesavealpha($temporaryGDResource, true);
                }
                imagecopyresampled($temporaryGDResource, $imageObject->GDResource, 0, 0, 0, 0, $imageObject->width, $imageObject->height, $imageObject->width, $imageObject->height);

                if ($interlace) {
                    imageinterlace($temporaryGDResource, true);
                }
                switch ($fileType) {
                    case 'jpg':
                    case 'jpeg':
                        imagejpeg($temporaryGDResource, $cacheFilePath, $jpegQuality);
                        break;
                    case 'png':
                        imagepng($temporaryGDResource, $cacheFilePath);
                        break;

                    case 'gif':
                        imagegif($temporaryGDResource, $cacheFilePath);
                        break;

                    case 'bmp':
                        if (!function_exists('imagebmp')) {
                            include_once('function.imagebmp.php');
                        }
                        imagebmp($temporaryGDResource, $cacheFilePath);
                        break;
                }
                chmod($cacheFilePath, $this->defaultCachePermissions);
                $imageObject->cacheExists = true;
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
