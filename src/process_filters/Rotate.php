<?php namespace ImageProcess;

class Rotate extends Filter
{
    protected $angle;

    protected function processObject(ImageProcess $imageProcess, ImageObject $object1, ImageObject $object2 = null)
    {
        $newObject = $imageProcess->getEmptyImageObject($object1->width, $object1->height);

        $newObject->GDResource = imagerotate($object1->GDResource, $this->angle, -1);

        return $newObject;
    }
}

