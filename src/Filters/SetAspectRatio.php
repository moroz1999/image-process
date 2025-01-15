<?php
declare(strict_types=1);

namespace ImageProcess\Filters;

use ImageProcess\Filter;
use ImageProcess\ImageObject;
use ImageProcess\ImageProcess;

class SetAspectRatio extends Filter
{
    public ?string $color;
    public ?string $mode = 'contain';
    public ?string $valign = 'center';
    public ?string $halign = 'center';
    public ?float $aspectRatio;

    protected function processObject(ImageProcess $imageProcess, ImageObject $object1, ?ImageObject $object2 = null): ?ImageObject
    {
        $originalWidth = $object1->getWidth();
        $originalHeight = $object1->getHeight();
        $requestedAspect = $this->aspectRatio;

        $originalAspect = $originalWidth / $originalHeight;

        if ($this->mode === 'contain') {
            if ($originalAspect > $requestedAspect) {
                $newWidth = $object1->getWidth();
                $newHeight = $newWidth / $requestedAspect;
            } else {
                $newHeight = $object1->getHeight();
                $newWidth = $newHeight * $requestedAspect;
            }

        } elseif ($this->mode === 'cover') {
            if ($originalAspect > $requestedAspect) {
                $newHeight = $object1->getHeight();
                $newWidth = $newHeight * $requestedAspect;
            } else {
                $newWidth = $object1->getWidth();
                $newHeight = $newWidth / $requestedAspect;
            }
        }

        $newWidth = (int)ceil($newWidth);
        $newHeight = (int)ceil($newHeight);

        $newObject = $imageProcess->getEmptyImageObject($newWidth, $newHeight);
        if ($this->color) {
            $color = hexdec($this->color);
        } else {
            $color = imagecolorallocatealpha($newObject->getGDResource(), 0, 0, 0, 127);
        }
        imagefilledrectangle($newObject->getGDResource(), 0, 0, $newWidth, $newHeight, $color);

        $sourceX = 0;
        if ($object1->getWidth() > $newWidth) {
            $sourceX = ($object1->getWidth() - $newWidth) / 2;
        }

        $sourceY = 0;
        if ($object1->getHeight() > $newHeight) {
            $sourceY = ($object1->getHeight() - $newHeight) / 2;
        }

        if ($object1->getWidth() < $newWidth) {
            $newWidth = $object1->getWidth();
        }
        if ($object1->getHeight() < $newHeight) {
            $newHeight = $object1->getHeight();
        }
        if ($this->halign === 'left') {
            $destinationX = 0;
        } elseif ($this->halign === 'center') {
            $destinationX = ($newObject->getWidth() - $newWidth) / 2;
        } elseif ($this->halign === 'right') {
            $destinationX = ($newObject->getWidth() - $newWidth);
        }

        if ($this->valign === 'top') {
            $destinationY = 0;
        } elseif ($this->valign === 'center') {
            $destinationY = ($newObject->getHeight() - $newHeight) / 2;
        } elseif ($this->valign === 'bottom') {
            $destinationY = ($newObject->getHeight() - $newHeight);
        }
        imagealphablending($newObject->getGDResource(), true);
        imagecopy($newObject->getGDResource(), $object1->getGDResource(), $destinationX, $destinationY, $sourceX, $sourceY, $newWidth, $newHeight);

        return $newObject;
    }
}