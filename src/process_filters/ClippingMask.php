<?php namespace ImageProcess;

class ClippingMask extends Filter
{
    public $top;
    public $left;
    public $bottom;
    public $right;

    // args: imageProcess, incomingObject, incomingObject2
    protected function processObject(ImageProcess $imageProcess, ImageObject $object1, ImageObject $object2 = null)
    {
        if (!$object2) {
            return false;
        }
        $source = $imageProcess->getImageObjectCopy($object1)->getGDResource();
        $mask = $object2->getGDResource();

        // Get sizes and set up new picture
        $width = imagesx($source);
        $height = imagesy($source);

        $newImageObject = $imageProcess->getEmptyImageObject($width, $height);
        imagealphablending($newImageObject->getGDResource(), true);
        $newImage = $newImageObject->getGDResource();
        imagesavealpha($newImage, true);
        imagefill($newImage, 0, 0, imagecolorallocatealpha($newImage, 0, 0, 0, 127));

        // Crop mask if necessary
        $this->adjustMaskImage($mask, $width, $height);

        // Perform pixel-based alpha map application
        for ($x = 0; $x < $width; ++$x) {
            for ($y = 0; $y < $height; ++$y) {
                $maskColor = imagecolorat($mask, $x, $y);
                $subjectColor = imagecolorat($source, $x, $y);
                $subjectAlpha = ($subjectColor >> 24) & 0x7f;
                $subjectColorNoAlpha = $subjectColor & 16777215;
                if ($subjectAlpha == 127) {
                    continue;
                }
                $alpha = $subjectAlpha + (($maskColor >> 24) & 0x7f);
                if ($alpha >= 127) {
                    $newColorIndex = 127 << 24;
                } else {
                    $newColorIndex = $subjectColorNoAlpha + ($alpha << 24);
                }
                imagesetpixel($newImage, $x, $y, $newColorIndex);
            }
        }
        return $newImageObject;
    }

    protected function adjustMaskImage(&$image, $width, $height)
    {
        $originalWidth = imagesx($image);
        $originalHeight = imagesy($image);

        if ($originalHeight !== $height && $originalWidth !== $width) {
            $newImage = imagecreatetruecolor($width, $height);
            imagesavealpha($newImage, true);
            imagefill($newImage, 0, 0, imagecolorallocatealpha($newImage, 0, 0, 0, 127));

            $sourceX = 0;
            $sourceY = 0;
            $destinationX = 0;
            $destinationY = 0;

            if ($this->left === null && $this->right === null) {
                if ($originalWidth > $width) {
                    $sourceX = ($originalWidth - $width) / 2;
                } else {
                    $destinationX = ($width - $originalWidth) / 2;
                }
            } else {
                // manual positioning
                if ($this->left !== null) {
                    $destinationX = $this->left;
                } else {
                    $destinationX = $width - $this->right - $originalWidth;
                }
            }

            if ($this->top === null && $this->bottom === null) {
                if ($originalHeight > $height) {
                    $sourceY = ($originalHeight - $height) / 2;
                } else {
                    $destinationY = ($height - $originalHeight) / 2;
                }
            } else {
                // manual positioning
                if ($this->top !== null) {
                    $destinationY = $this->top;
                } else {
                    $destinationY = $height - $this->bottom - $originalHeight;
                }
            }
            imagecopy($newImage, $image, $destinationX, $destinationY, $sourceX, $sourceY, $originalWidth, $originalHeight);
            imagedestroy($image);
            $image = $newImage;
        }
    }
}

