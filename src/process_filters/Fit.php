<?php namespace ImageProcess;

class Fit extends Filter
{
    protected $width;
    protected $height;

    protected function processObject(ImageProcess $imageProcess, ImageObject $object1, ImageObject $object2 = null)
    {
        if (!$this->width) {
            $this->width = $object1->getWidth();
        }
        if (!$this->height) {
            $this->height = $object1->getHeight();
        }

        if ($object1->getWidth() <= $this->width || $object1->getHeight() <= $this->height) {
            return $object1;
        }

        if ($object1->getWidth() > $this->width) {
            $newWidth = $this->width;
            $newHeight = $object1->getHeight() * $this->width / $object1->getWidth();
        }

        if ($newHeight < $this->height) {
            $newHeight = $this->height;
            $newWidth = $object1->getWidth() * $this->height / $object1->getHeight();
        }

        $newObject = $imageProcess->getEmptyImageObject($newWidth, $newHeight);
        imagegammacorrect($object1->getGDResource(), 2.2, 1.0);
        imagecopyresampled($newObject->getGDResource(), $object1->getGDResource(), 0, 0, 0, 0, $newWidth, $newHeight, $object1->getWidth(), $object1->getHeight());
        imagegammacorrect($newObject->getGDResource(), 1.0, 2.2);

        return $newObject;
    }
}
