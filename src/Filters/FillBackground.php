<?php
declare(strict_types=1);

namespace ImageProcess\Filters;

use ImageProcess\Filter;
use ImageProcess\ImageObject;
use ImageProcess\ImageProcess;

class FillBackground extends Filter
{
    protected function processObject(ImageProcess $imageProcess, ImageObject $object1, ?ImageObject $object2 = null): ?ImageObject
    {
        $width = $object1->getWidth();
        $height = $object1->getHeight();

        $newObject = $imageProcess->getEmptyImageObject($width, $height);
        if (isset($this->color) && $this->color !== '') {
            $colors = $this->hex2rgb($this->color);
            $color = imagecolorallocate($newObject->getGDResource(), $colors[0], $colors[1], $colors[2]);
        } else {
            $color = imagecolorallocate($newObject->getGDResource(), 255, 255, 255);
        }
        imagefilledrectangle($newObject->getGDResource(), 0, 0, $width, $height, $color);

        imagealphablending($newObject->getGDResource(), true);
        imagecopy(
            $newObject->getGDResource(),
            $object1->getGDResource(),
            0,
            0,
            0,
            0,
            $width,
            $height
        );
        return $newObject;
    }

    protected function hex2rgb($hex)
    {
        $hex = str_replace("#", "", $hex);

        if (strlen($hex) === 3) {
            $r = hexdec($hex[0] . $hex[0]);
            $g = hexdec($hex[1] . $hex[1]);
            $b = hexdec($hex[2] . $hex[2]);
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }
        return [$r, $g, $b];
    }
}

