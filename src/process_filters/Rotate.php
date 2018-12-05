<?php namespace ImageProcess;

class Rotate extends Filter
{
    protected $angle;

    protected function processObject(ImageProcess $imageProcess, ImageObject $object1, ImageObject $object2 = null)
    {
        $newObject = $imageProcess->getEmptyImageObject($object1->getWidth(), $object1->getHeight());

        $newObject->getGDResource() = imagerotate($object1->getGDResource(), $this->angle, -1);

        return $newObject;
    }
}

