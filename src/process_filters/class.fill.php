<?php namespace ImageProcess;

class Fill extends Filter
{
    protected $width;
    protected $height;

    protected function processObject(ImageProcess $imageProcess, ImageObject $object1, ImageObject $object2 = null)
    {
        $targetHeight = (int)$this->height;
        $targetWidth = (int)$this->width;

        if ($object1->width != $targetWidth && $object1->height != $targetHeight) {
            $resultImage = $imageProcess->getEmptyImageObject($targetWidth, $targetHeight);
            $ratio = $object1->width / $object1->height;

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
            imagegammacorrect($object1->GDResource, 2.2, 1.0);
            imagecopyresampled($resultImage->GDResource, $object1->GDResource, -$offsetX, -$offsetY, 0, 0, $newWidth, $newHeight, $object1->width, $object1->height);
            imagegammacorrect($resultImage->GDResource, 1.0, 2.2);
        } else {
            $resultImage = $object1;
        }
        return $resultImage;
    }
}
