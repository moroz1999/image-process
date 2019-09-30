<?php namespace ImageProcess;

class Fill extends Filter
{
    protected $width;
    protected $height;

    protected function processObject(ImageProcess $imageProcess, ImageObject $object1, ImageObject $object2 = null)
    {
        $targetHeight = (int)$this->height;
        $targetWidth = (int)$this->width;

        if ($object1->getWidth() != $targetWidth && $object1->getHeight() != $targetHeight) {
            $resultImage = $imageProcess->getEmptyImageObject($targetWidth, $targetHeight);
            $ratio = $object1->getWidth() / $object1->getHeight();

            if ($ratio >= $targetWidth / $targetHeight) {
                // by height
                $newHeight = $targetHeight;
                $newWidth = $newHeight * $ratio;
                $offsetX = round(abs($newWidth - $targetWidth) / 2);
                $offsetY = 0;
            } else {
                // by width
                $newWidth = $targetWidth;
                $newHeight = $newWidth / $ratio;
                $offsetX = 0;
                $offsetY = round(abs($newHeight - $targetHeight) / 2);
            }
            imagecopyresampled($resultImage->getGDResource(), $object1->getGDResource(), -$offsetX, -$offsetY, 0, 0, $newWidth, $newHeight, $object1->getWidth(), $object1->getHeight());
        } else {
            $resultImage = $object1;
        }
        return $resultImage;
    }
}
