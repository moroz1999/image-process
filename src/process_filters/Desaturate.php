<?php namespace ImageProcess;

class Desaturate extends Filter
{
    protected function processObject(ImageProcess $imageProcess, ImageObject $object1, ImageObject $object2 = null)
    {
        $newObject = $imageProcess->getImageObjectCopy($object1);
        imagefilter($newObject->GDResource, IMG_FILTER_GRAYSCALE);

        return $newObject;
    }
}

