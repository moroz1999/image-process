<?php namespace ImageProcess;

/**
 * imageProcessContrast
 */
class Contrast extends Filter
{
    protected $amount = 50; // filter parameter. 1-100

    protected function processObject(ImageProcess $imageProcess, ImageObject $object1, ImageObject $object2 = null)
    {
        $newObject = $imageProcess->getImageObjectCopy($object1);
        $this->amount = 100 - min(max($this->amount, 1), 100) * 2;
        imagefilter($newObject->GDResource, IMG_FILTER_CONTRAST, $this->amount);
        return $newObject;
    }
}

