<?php
declare(strict_types=1);

namespace ImageProcess\Filters;

use ImageProcess\Filter;
use ImageProcess\ImageObject;
use ImageProcess\ImageProcess;

class StrictResize extends Filter
{
    protected $width;
    protected $height;

    protected function processObject(ImageProcess $imageProcess, ImageObject $object1, ?ImageObject $object2 = null): ?ImageObject
    {
        $newWidth = $this->width;
        $newHeight = $this->height;

        $newObject = $imageProcess->getEmptyImageObject($newWidth, $newHeight);

        imagecopyresampled($newObject->getGDResource(), $object1->getGDResource(), 0, 0, 0, 0, $newWidth, $newHeight, $object1->getWidth(), $object1->getHeight());
        return $newObject;
    }
}
