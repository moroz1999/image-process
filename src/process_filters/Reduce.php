<?php namespace ImageProcess;

class Reduce extends Filter
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

        if ($this->width != '' && $object1->getWidth() > $this->width) {
            $newWidth = $this->width;
            $newHeight = $object1->getHeight() * $this->width / $object1->getWidth();
        } else {
            if ($object1->getHeight() > $this->height) {
                $newHeight = $this->height;
                $newWidth = $object1->getWidth() * $this->height / $object1->getHeight();
            } else {
                return $object1;
            }
        }

        if ($newHeight > $this->height) {
            $newHeight = $this->height;
            $newWidth = $object1->getWidth() * $this->height / $object1->getHeight();
        }

        $newObject = $imageProcess->getEmptyImageObject($newWidth, $newHeight);

        imagecopyresampled($newObject->getGDResource(), $object1->getGDResource(), 0, 0, 0, 0, $newWidth, $newHeight, $object1->getWidth(), $object1->getHeight());

        return $newObject;
    }
}
