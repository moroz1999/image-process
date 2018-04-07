<?php namespace ImageProcess;

class Interlace extends Filter
{
    protected function processObject(ImageProcess $imageProcess, ImageObject $object1, ImageObject $object2 = null)
    {
        $newObject = $imageProcess->getImageObjectCopy($object1);
        imageinterlace($newObject->GDResource, true);

        return $newObject;
    }
}

