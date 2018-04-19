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
        $exportOperation['cacheExists'] = null;
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
        if ($cacheGroup) {
            $cacheFileName = $cacheGroup . '/' . $cacheFileName;
        }
        $cacheFileName = $this->cachePath . $cacheFileName;

        $exportOperation['cacheFileName'] = $cacheFileName;
        $exportOperation['cacheGroup'] = $cacheGroup;
        if (!file_exists($cacheFileName)) {
            $exportOperation['cacheExists'] = false;
        } else {
            $exportOperation['cacheExists'] = true;
        }

        $this->exportList[] = $exportOperation;
        return $exportOperation;
    }

    protected function exportImage($exportOperation)
    {
        $objectName = $exportOperation['objectName'];
        $fileType = $exportOperation['fileType'];
        $fileName = $exportOperation['fileName'];
        $cacheFileName = $exportOperation['cacheFileName'];
        $cacheGroup = $exportOperation['cacheGroup'];
        $cacheExists = $exportOperation['cacheExists'];
        $jpegQuality = $exportOperation['jpegQuality'];
        $interlace = $exportOperation['interlace'];

        if (!$cacheExists || !$this->imagesCaching) {
            if ($cacheGroup) {
                $groupDir = $this->cachePath . $cacheGroup . '/';
                if (!is_dir($groupDir)) {
                    mkdir($groupDir . '/');
                    chmod($groupDir, $this->defaultCachePermissions);
                } else {
                    touch($groupDir);
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
                        imagejpeg($temporaryGDResource, $cacheFileName, $jpegQuality);
                        break;
                    case 'png':
                        imagepng($temporaryGDResource, $cacheFileName);
                        break;

                    case 'gif':
                        imagegif($temporaryGDResource, $cacheFileName);
                        break;

                    case 'bmp':
                        imagebmp($temporaryGDResource, $cacheFileName);
                        break;
                }
                chmod($cacheFileName, $this->defaultCachePermissions);
                $imageObject->cacheExists = true;
            }
        }

        if ($fileName != '') {
            $fileContents = file_get_contents($cacheFileName);
            file_put_contents($fileName, $fileContents);
        }
    }

    protected function checkCachePath()
    {
        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath);
            chmod($this->cachePath, $this->defaultCachePermissions);
        }
    }

    public function setDefaultCachePermissions($defaultCachePermissions)
    {
        $this->defaultCachePermissions = $defaultCachePermissions;
    }
}
