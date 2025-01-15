<?php
declare(strict_types=1);

namespace ImageProcess\Filters;

use ImageProcess\Filter;
use ImageProcess\ImageObject;
use ImageProcess\ImageProcess;

class Colorize extends Filter
{
    protected ?int $red;
    protected ?int $green;
    protected ?int $blue;

    protected function processObject(ImageProcess $imageProcess, ImageObject $object1, ?ImageObject $object2 = null): ?ImageObject
    {
        $outgoingObject = $imageProcess->getImageObjectCopy($this->incomingObject);
        imagefilter($outgoingObject->getGDResource(), IMG_FILTER_COLORIZE, $this->red, $this->green, $this->blue);

        return $outgoingObject->getGDResource();
    }
}

