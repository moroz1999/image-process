<?php
declare(strict_types=1);

namespace ImageProcess\Filters;

use ImageProcess\Filter;
use ImageProcess\ImageObject;
use ImageProcess\ImageProcess;

/**
 * Draws another image on image
 * Usage example: image=images/watermark123.png,bottom=20,right=20
 */
class Watermark extends Filter
{
    // parameters
    public string $image;
    public ?int $top;
    public ?int $left;
    public ?int $bottom;
    public ?int $right;

    protected function processObject(ImageProcess $imageProcess, ImageObject $object1, ?ImageObject $object2 = null): ?ImageObject
    {
        if (!file_exists($this->image)) {
            return null;
        }

        $newObject = $imageProcess->getImageObjectCopy($object1);
        $image = $newObject->getGDResource();

        $watermark = imagecreatefrompng($this->image);

        imagealphablending($image, true);
        imagesavealpha($image, true);

        $watermarkWidth = imagesx($watermark);
        $watermarkHeight = imagesy($watermark);

        $imageWidth = imagesx($image);
        $imageHeight = imagesy($image);

        // process params
        if ($this->top === null) {
            $y = $imageHeight - $this->bottom - $watermarkHeight;
        } else {
            $y = $this->top + $watermarkHeight;
        }
        $x = $this->left ?? ($imageWidth - $this->right - $watermarkWidth);

        imagecopy($image, // destination
            $watermark, // source
            $x, $y, // destination x and y
            0, 0, // source x and y
            $watermarkWidth, $watermarkHeight // width and height of the area of the source to copy
        );
        return $newObject;
    }
}

