<?php
declare(strict_types=1);

namespace ImageProcess\Filters;

use ImageProcess\Filter;
use ImageProcess\ImageObject;
use ImageProcess\ImageProcess;

class Interlace extends Filter
{
    protected function processObject(ImageProcess $imageProcess, ImageObject $object1, ?ImageObject $object2 = null): ?ImageObject
    {
        $newObject = $imageProcess->getImageObjectCopy($object1);
        imageinterlace($newObject->getGDResource(), true);

        return $newObject;
    }
}

