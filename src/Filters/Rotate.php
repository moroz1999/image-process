<?php
declare(strict_types=1);

namespace ImageProcess\Filters;

use ImageProcess\Filter;
use ImageProcess\ImageObject;
use ImageProcess\ImageProcess;

class Rotate extends Filter
{
    protected ?float $angle;

    protected function processObject(ImageProcess $imageProcess, ImageObject $object1, ?ImageObject $object2 = null): ?ImageObject
    {
        $newObject = $imageProcess->getEmptyImageObject($object1->getWidth(), $object1->getHeight());

        $newObject->setGDResource(imagerotate($object1->getGDResource(), $this->angle, -1));

        return $newObject;
    }
}

