<?php
declare(strict_types=1);

namespace ImageProcess\Filters;

use ImageProcess\Filter;
use ImageProcess\ImageObject;
use ImageProcess\ImageProcess;

class Corner extends Filter
{
    protected ?int $radius;
    protected ?string $positions;
    protected ?string $background;

    protected function processObject(ImageProcess $imageProcess, ImageObject $object1, ?ImageObject $object2 = null): ?ImageObject
    {
        $radius = $this->radius;
        $doubleRadius = $radius * 2;

        if ($this->positions) {
            $positions = $this->positions;
        } else {
            $positions = '1111';
        }

        if ($background = $this->background) {
            $r = hexdec(substr($background, 0, 2));
            $g = hexdec(substr($background, 2, 2));
            $b = hexdec(substr($background, 4, 2));
            $transparentColor = imagecolorallocate($object1->getGDResource(), $r, $g, $b);
        } else {
            $transparentColor = imagecolorallocatealpha($object1->getGDResource(), 255, 255, 255, 127);
        }

        if ($radius > 15) {
            $q = 50;
        } else {
            $q = 0;
        }

        $tempImage = $imageProcess->getEmptyImageObject($object1->getWidth() * 2, $object1->getHeight() * 2);

        imagecopyresampled($tempImage->getGDResource(), $object1->getGDResource(), 0, 0, 0, 0, $tempImage->getWidth(), $tempImage->getHeight(), $object1->getWidth(), $object1->getHeight());

        imageAlphaBlending($tempImage->getGDResource(), false);
        $powRadius = $doubleRadius ** 2;


        if (str_starts_with($positions, '1')) {
            //left top
            for ($x = 0; $x < $doubleRadius; $x++) {
                for ($y = 0; $y < $doubleRadius; $y++) {
                    $pif = (($doubleRadius - $x) ** 2) + (($doubleRadius - $y) ** 2);
                    if ($pif + $q >= $powRadius) {
                        imagesetpixel($tempImage->getGDResource(), $x, $y, $transparentColor);
                    }
                }
            }
        }

        if ($positions[1] === '1') {
            //right top
            for ($x = $tempImage->getWidth(); $tempImage->getWidth() - $x < $doubleRadius; $x--) {
                for ($y = 0; $y < $doubleRadius; $y++) {
                    $pif = (($doubleRadius - ($tempImage->getWidth() - $x)) ** 2) + (($doubleRadius - $y) ** 2);
                    if ($pif + $q >= $powRadius) {
                        imagesetpixel($tempImage->getGDResource(), $x, $y, $transparentColor);
                    }
                }
            }
        }

        if ($positions[2] === '1') {
            //right bottom
            for ($x = $tempImage->getWidth(); $tempImage->getWidth() - $x < $doubleRadius; $x--) {
                for ($y = $tempImage->getHeight(); $tempImage->getHeight() - $y < $doubleRadius; $y--) {
                    $pif = (($doubleRadius - ($tempImage->getWidth() - $x)) ** 2) + (($doubleRadius - ($tempImage->getHeight() - $y)) ** 2);
                    if ($pif + $q >= $powRadius) {
                        imagesetpixel($tempImage->getGDResource(), $x, $y, $transparentColor);
                    }
                }
            }
        }

        if ($positions[3] === '1') {
            //left bottom
            for ($x = 0; $x < $doubleRadius; $x++) {
                for ($y = $tempImage->getHeight(); $tempImage->getHeight() - $y < $doubleRadius; $y--) {
                    $pif = (($doubleRadius - $x) ** 2) + (($doubleRadius - ($tempImage->getHeight() - $y)) ** 2);
                    if ($pif + $q >= $powRadius) {
                        imagesetpixel($tempImage->getGDResource(), $x, $y, $transparentColor);
                    }
                }
            }
        }


        $resultImage = $imageProcess->getEmptyImageObject($object1->getWidth(), $object1->getHeight());

        imagecopyresampled($resultImage->getGDResource(), $tempImage->getGDResource(), 0, 0, 0, 0, $object1->getWidth(), $object1->getHeight(), $tempImage->getWidth(), $tempImage->getHeight());

        return $resultImage;
    }
}

