<?php
declare(strict_types=1);

namespace ImageProcess\Filters;

use ImageProcess\Filter;
use ImageProcess\ImageObject;
use ImageProcess\ImageProcess;

class Desaturate extends Filter
{
    protected function processObject(ImageProcess $imageProcess, ImageObject $object1, ?ImageObject $object2 = null): ?ImageObject
    {
        $newObject = $imageProcess->getImageObjectCopy($object1);
        imagefilter($newObject->getGDResource(), IMG_FILTER_GRAYSCALE);

        return $newObject;
    }
}

