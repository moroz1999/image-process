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
        $newObject = $imageProcess->getEmptyImageObject($object1->width, $object1->height);
        imagealphablending($newObject->GDResource, true);
        imagefill($newObject->GDResource, 0, 0, imagecolorallocatealpha($newObject->GDResource, 0, 0, 0, 127));

        if (!$this->beneath) {
            imagecopy($newObject->GDResource, $object1->GDResource, 0, 0, 0, 0, $object1->width, $object1->height);
        }

        $sourceX = 0;
        $sourceY = 0;
        $destinationX = 0;
        $destinationY = 0;

        $originalWidth = $object2->width;
        $originalHeight = $object2->height;

        if ($this->left === null && $this->right === null) {
            if ($originalWidth > $object1->width) {
                $sourceX = ($originalWidth - $object1->width) / 2;
            } else {
                $destinationX = ($object1->width - $originalWidth) / 2;
            }
        } else {
            // manual positioning
            if ($this->left !== null) {
                $destinationX = $this->left;
            } else {
                $destinationX = $object1->width - $this->right - $originalWidth;
            }
        }

        if ($this->top === null && $this->bottom === null) {
            if ($originalHeight > $object1->height) {
                $sourceY = ($originalHeight - $object1->height) / 2;
            } else {
                $destinationY = ($object1->height - $originalHeight) / 2;
            }
        } else {
            // manual positioning
            if ($this->top !== null) {
                $destinationY = $this->top;
            } else {
                $destinationY = $object1->height - $this->bottom - $originalHeight;
            }
        }

        imagecopy($newObject->GDResource, $object2->GDResource, $destinationX, $destinationY, $sourceX, $sourceY, $object2->width, $object2->height);

        if ($this->beneath) {
            imagecopy($newObject->GDResource, $object1->GDResource, 0, 0, 0, 0, $object1->width, $object1->height);
        }
        return $newObject;
    }
}

