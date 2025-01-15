<?php
declare(strict_types=1);

namespace ImageProcess\Filters;

use ImageProcess\Filter;
use ImageProcess\ImageObject;
use ImageProcess\ImageProcess;

class Reduce extends Filter
{
    protected ?int $width = null;
    protected ?int $height = null;

    protected function processObject(ImageProcess $imageProcess, ImageObject $object1, ?ImageObject $object2 = null): ?ImageObject
    {
        if (!$this->width) {
            $this->width = $object1->getWidth();
        }
        if (!$this->height) {
            $this->height = $object1->getHeight();
        }

        if ($this->width !== null && $object1->getWidth() > $this->width) {
            $newWidth = $this->width;
            $newHeight = $object1->getHeight() * $this->width / $object1->getWidth();
        } elseif ($object1->getHeight() > $this->height) {
            $newHeight = $this->height;
            $newWidth = $object1->getWidth() * $this->height / $object1->getHeight();
        } else {
            return $object1;
        }

        if ($newHeight > $this->height) {
            $newHeight = $this->height;
            $newWidth = $object1->getWidth() * $this->height / $object1->getHeight();
        }

        $newWidth = (int)ceil($newWidth);
        $newHeight = (int)ceil($newHeight);
        $newObject = $imageProcess->getEmptyImageObject($newWidth, $newHeight);

        imagecopyresampled($newObject->getGDResource(), $object1->getGDResource(), 0, 0, 0, 0, $newWidth, $newHeight, $object1->getWidth(), $object1->getHeight());

        return $newObject;
    }
}
