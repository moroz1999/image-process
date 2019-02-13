<?php namespace ImageProcess;

/**
 * imageProcessSharpen
 * Based on the sharpening method in Kohana framework
 */
class Sharpen extends Filter
{
    protected $amount = 50; // filter parameter. 1-100

    protected function processObject(ImageProcess $imageProcess, ImageObject $object1, ImageObject $object2 = null)
    {
        $newObject = $imageProcess->getImageObjectCopy($object1);
        $this->amount = min(max($this->amount, 1), 100);
        $divisor = round(abs(-18 + ($this->amount * 0.08)), 2); // 10-18
        $matrix = [
            [
                -1,
                -1,
                -1,
            ],
            [
                -1,
                $divisor,
                -1,
            ],
            [
                -1,
                -1,
                -1,
            ],
        ];
        imageconvolution($newObject->getGDResource(), $matrix, $divisor - 8, 0);
        return $newObject;
    }
}

