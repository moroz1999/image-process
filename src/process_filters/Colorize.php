<?php namespace ImageProcess;

class Colorize extends Filter
{
    protected $red;
    protected $green;
    protected $blue;

    protected function processObject(ImageProcess $imageProcess, ImageObject $object1, ImageObject $object2 = null)
    {
        $outgoingObject = $imageProcess->getImageObjectCopy($this->incomingObject);
        imagefilter($outgoingObject->getGDResource(), IMG_FILTER_COLORIZE, $this->red, $this->green, $this->blue);

        return $outgoingObject->getGDResource();
    }
}

