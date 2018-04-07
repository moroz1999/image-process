<?php namespace ImageProcess;

class Reduce extends Filter
{
    protected $width;
    protected $height;

    protected function processObject(ImageProcess $imageProcess, ImageObject $object1, ImageObject $object2 = null)
    {
        if (!$this->width) {
            $this->width = $object1->width;
        }
        if (!$this->height) {
            $this->height = $object1->height;
        }

        if ($this->width != '' && $object1->width > $this->width) {
            $newWidth = $this->width;
            $newHeight = $object1->height * $this->width / $object1->width;
        } else {
            if ($object1->height > $this->height) {
                $newHeight = $this->height;
                $newWidth = $object1->width * $this->height / $object1->height;
            } else {
                return $object1;
            }
        }

        if ($newHeight > $this->height) {
            $newHeight = $this->height;
            $newWidth = $object1->width * $this->height / $object1->height;
        }

        $newObject = $imageProcess->getEmptyImageObject($newWidth, $newHeight);

        imagegammacorrect($object1->GDResource, 2.2, 1.0);
        imagecopyresampled($newObject->GDResource, $object1->GDResource, 0, 0, 0, 0, $newWidth, $newHeight, $object1->width, $object1->height);
        imagegammacorrect($newObject->GDResource, 1.0, 2.2);

        return $newObject;
    }
}
