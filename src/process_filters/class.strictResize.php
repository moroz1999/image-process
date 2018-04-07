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

        imagegammacorrect($object1->GDResource, 2.2, 1.0);
        imagecopyresampled($newObject->GDResource, $object1->GDResource, 0, 0, 0, 0, $newWidth, $newHeight, $object1->width, $object1->height);
        imagegammacorrect($newObject->GDResource, 1.0, 2.2);
        return $newObject;
    }
}
