<?php namespace ImageProcess;

class Merge extends Filter
{
    public $top;
    public $left;
    public $bottom;
    public $right;
    public $beneath = false;

    protected function processObject(ImageProcess $imageProcess, ImageObject $object1, ImageObject $object2 = null)
    {
        if (!$object2) {
            return $object1;
        }
        $newObject = $imageProcess->getEmptyImageObject($object1->getWidth(), $object1->getHeight());
        imagealphablending($newObject->getGDResource(), true);
        imagefill($newObject->getGDResource(), 0, 0, imagecolorallocatealpha($newObject->getGDResource(), 0, 0, 0, 127));

        if (!$this->beneath) {
            imagecopy($newObject->getGDResource(), $object1->getGDResource(), 0, 0, 0, 0, $object1->getWidth(), $object1->getHeight());
        }

        $sourceX = 0;
        $sourceY = 0;
        $destinationX = 0;
        $destinationY = 0;

        $originalWidth = $object2->getWidth();
        $originalHeight = $object2->getHeight();

        if ($this->left === null && $this->right === null) {
            if ($originalWidth > $object1->getWidth()) {
                $sourceX = ($originalWidth - $object1->getWidth()) / 2;
            } else {
                $destinationX = ($object1->getWidth() - $originalWidth) / 2;
            }
        } else {
            // manual positioning
            if ($this->left !== null) {
                $destinationX = $this->left;
            } else {
                $destinationX = $object1->getWidth() - $this->right - $originalWidth;
            }
        }

        if ($this->top === null && $this->bottom === null) {
            if ($originalHeight > $object1->getHeight()) {
                $sourceY = ($originalHeight - $object1->getHeight()) / 2;
            } else {
                $destinationY = ($object1->getHeight() - $originalHeight) / 2;
            }
        } else {
            // manual positioning
            if ($this->top !== null) {
                $destinationY = $this->top;
            } else {
                $destinationY = $object1->getHeight() - $this->bottom - $originalHeight;
            }
        }

        imagecopy($newObject->getGDResource(), $object2->getGDResource(), $destinationX, $destinationY, $sourceX, $sourceY, $object2->getWidth(), $object2->getHeight());

        if ($this->beneath) {
            imagecopy($newObject->getGDResource(), $object1->getGDResource(), 0, 0, 0, 0, $object1->getWidth(), $object1->getHeight());
        }
        return $newObject;
    }
}

