<?php
declare(strict_types=1);

namespace ImageProcess\Filters;

use ImageProcess\Filter;
use ImageProcess\ImageObject;
use ImageProcess\ImageProcess;

class AspectedResize extends Filter
{
    protected ?int $width = null;
    protected ?int $height = null;
    protected ?int $interpolation = null;

    protected function processObject(ImageProcess $imageProcess, ImageObject $object1, ?ImageObject $object2 = null): ?ImageObject
    {
        if ($this->width) {
            $newWidth = $this->width;
            $newHeight = $object1->getHeight() * $this->width / $object1->getWidth();
        } else {
            $newHeight = $this->height;
            $newWidth = $object1->getWidth() * $this->height / $object1->getHeight();
        }
        if ($this->height) {
            if ($newHeight > $this->height) {
                $newHeight = $this->height;
                $newWidth = $object1->getWidth() * $this->height / $object1->getHeight();
            }
        }
        $newWidth = (int)ceil($newWidth);
        $newHeight = (int)ceil($newHeight);
        $newObject = $imageProcess->getEmptyImageObject($newWidth, $newHeight);

        if ($this->interpolation) {
            imagesetinterpolation($object1->getGDResource(), $this->interpolation);
            imagesetinterpolation($newObject->getGDResource(), $this->interpolation);
        }
        imagecopyresampled($newObject->getGDResource(), $object1->getGDResource(), 0, 0, 0, 0, $newWidth, $newHeight, $object1->getWidth(), $object1->getHeight());

        return $newObject;
    }
}