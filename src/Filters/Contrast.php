<?php
declare(strict_types=1);

namespace ImageProcess\Filters;

use ImageProcess\Filter;
use ImageProcess\ImageObject;
use ImageProcess\ImageProcess;

/**
 * imageProcessContrast
 */
class Contrast extends Filter
{
    protected int $amount = 50; // filter parameter. 1-100

    protected function processObject(ImageProcess $imageProcess, ImageObject $object1, ?ImageObject $object2 = null): ?ImageObject
    {
        $newObject = $imageProcess->getImageObjectCopy($object1);
        $this->amount = 100 - min(max($this->amount, 1), 100) * 2;
        imagefilter($newObject->getGDResource(), IMG_FILTER_CONTRAST, $this->amount);
        return $newObject;
    }
}

