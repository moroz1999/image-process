<?php namespace ImageProcess;

class StrictResize extends Filter
{
    protected $width;
    protected $height;

    protected function processObject(ImageProcess $imageProcess, ImageObject $object1, ImageObject $object2 = null)
    {
        $newWidth = $this->width;
        $newHeight = $this->height;

        $newObject = $imageProcess->getEmptyImageObject($newWidth, $newHeight);

        imagecopyresampled($newObject->getGDResource(), $object1->getGDResource(), 0, 0, 0, 0, $newWidth, $newHeight, $object1->getWidth(), $object1->getHeight());
        return $newObject;
    }
}
