<?php namespace ImageProcess;

class Dither extends Filter
{
    public $method = 'floyd';
    public $offset = .5;

    protected function processObject(ImageProcess $imageProcess, ImageObject $object1, ImageObject $object2 = null)
    {
        imagefilter($object1->getGDResource(), IMG_FILTER_GRAYSCALE);
        $width = $object1->getWidth();
        $height = $object1->getHeight();
        $pixels = array();
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $pixels[$x][$y] = imagecolorat($object1->getGDResource(), $x, $y) & 0xFFFFFF;
            }
        }

        $newObject = $imageProcess->getEmptyImageObject($width, $height);
        $black = imagecolorallocate($newObject->getGDResource(), 0, 0, 0); //background color.
        $white = imagecolorallocate($newObject->getGDResource(), 0xff, 0xff, 0xff);

        if ($this->method == 'atkinson') {
            for ($y = 0; $y < $height; $y++) {
                for ($x = 0; $x < $width; $x++) {
                    $old = $pixels[$x][$y];
                    if ($old > 0xffffff * $this->offset) {
                        $new = 0xffffff;
                        imagesetpixel($newObject->getGDResource(), $x, $y, $white);
                    } else {
                        $new = 0x000000;
                        imagesetpixel($newObject->getGDResource(), $x, $y, $black);

                    }
                    $quantizationError = $old - $new;
                    $errorDiffusion = (1 / 8) * $quantizationError;
                    if (isset($pixels[$x + 1])) {
                        $pixels[$x + 1][$y] += $errorDiffusion;
                    }
                    if (isset($pixels[$x + 2])) {
                        $pixels[$x + 2][$y] += $errorDiffusion;
                    }
                    if (isset($pixels[$x - 1][$y + 1])) {
                        $pixels[$x - 1][$y + 1] += $errorDiffusion;
                    }
                    if (isset($pixels[$x][$y + 1])) {
                        $pixels[$x][$y + 1] += $errorDiffusion;
                    }
                    if (isset($pixels[$x + 1][$y + 1])) {
                        $pixels[$x + 1][$y + 1] += $errorDiffusion;
                    }
                    if (isset($pixels[$x][$y + 2])) {
                        $pixels[$x][$y + 2] += $errorDiffusion;
                    }
                }
            }
        } elseif ($this->method == 'floyd') {
            $weight1 = 7 / 16;
            $weight2 = 3 / 16;
            $weight3 = 5 / 16;
            $weight4 = 1 / 16;
            for ($y = 0; $y < $height; $y++) {
                for ($x = 0; $x < $width; $x++) {
                    $old = $pixels[$x][$y];
                    if ($old >= 0xffffff * $this->offset) {
                        $new = 0xffffff;
                        imagesetpixel($newObject->getGDResource(), $x, $y, $white);
                    } else {
                        $new = 0x000000;
                        imagesetpixel($newObject->getGDResource(), $x, $y, $black);
                    }
                    $quantizationError = $old - $new;
                    if (isset($pixels[$x + 1])) {
                        $pixels[$x + 1][$y] += $weight1 * $quantizationError;
                    }
                    if (isset($pixels[$x - 1][$y + 1])) {
                        $pixels[$x - 1][$y + 1] += $weight2 * $quantizationError;
                    }
                    if (isset($pixels[$x][$y + 1])) {
                        $pixels[$x][$y + 1] += $weight3 * $quantizationError;
                    }
                    if (isset($pixels[$x + 1][$y + 1])) {
                        $pixels[$x + 1][$y + 1] += $weight4 * $quantizationError;
                    }
                }
            }
        } elseif ($this->method == 'bayer') {
            $bayerThresholdMap = [
                [15, 135, 45, 165],
                [195, 75, 225, 105],
                [60, 180, 30, 150],
                [240, 120, 210, 90]
            ];

            //            var map = Math.floor( (imageData.data[currentPixel] + bayerThresholdMap[x%4][y%4]) / 2 );
            //      imageData.data[currentPixel] = (map < threshold) ? 0 : 255;


            for ($y = 0; $y < $height; $y++) {
                for ($x = 0; $x < $width; $x++) {
                    $map = floor(($pixels[$x][$y] + $bayerThresholdMap[$x % 4][$y % 4] * 0xffff / 2));
                    $color = ($map < $this->offset * 0xffffff) ? 0 : 0xffffff;
                    imagesetpixel($newObject->getGDResource(), $x, $y, $color);
                }
            }
        }

        return $newObject;
    }
}