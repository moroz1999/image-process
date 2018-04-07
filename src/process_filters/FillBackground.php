<?php namespace ImageProcess;

class FillBackground extends Filter
{
    protected function processObject(ImageProcess $imageProcess, ImageObject $object1, ImageObject $object2 = null)
    {
        $width = $object1->width;
        $height = $object1->height;

        $newObject = $imageProcess->getEmptyImageObject($width, $height);
        if (isset($this->color) && $this->color != '') {
            $colors = $this->hex2rgb($this->color);
            $color = imagecolorallocate($newObject->GDResource, $colors[0], $colors[1], $colors[2]);
        } else {
            $color = imagecolorallocate($newObject->GDResource, 255, 255, 255);
        }
        imagefilledrectangle($newObject->GDResource, 0, 0, $width, $height, $color);

        imagealphablending($newObject->GDResource, true);
        imagecopy(
            $newObject->GDResource,
            $object1->GDResource,
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

        if (strlen($hex) == 3) {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }
        $rgb = array($r, $g, $b);
        return $rgb;
    }
}

