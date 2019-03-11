<?php namespace ImageProcess;

class Crop extends Filter
{
    public $soft;
    public $width;
    public $height;
    public $color;
    public $valign = 'center';
    public $halign = 'center';
    public $aspectRatio;

    protected function processObject(ImageProcess $imageProcess, ImageObject $object1, ImageObject $object2 = null)
    {
        //if image was smaller than width or height and soft mode is used,
        //then we still have to cut some of it to retain a requested aspect ration
        if ($this->soft && ($this->width > $object1->getWidth() || $this->height > $object1->getHeight())) {
            if (!empty($this->aspectRatio)) {
                $requestedAspect = $this->aspectRatio;
            } else {
                $requestedAspect = $this->width / $this->height;
            }

            if ($this->width > $object1->getWidth()) {
                $newWidth = $object1->getWidth();
                $newHeight = $newWidth / $requestedAspect;

                if ($newHeight > $object1->getHeight()) {
                    $newHeight = $object1->getHeight();
                    $newWidth = $newHeight * $requestedAspect;
                }

            } elseif ($this->height > $object1->getHeight()) {
                $newHeight = $object1->getHeight();
                $newWidth = $newHeight * $requestedAspect;

                if ($newWidth > $object1->getWidth()) {
                    $newWidth = $object1->getWidth();
                    $newHeight = $newWidth / $requestedAspect;
                }
            }

        } else {
            if ($this->width) {
                $newWidth = $this->width;
            } else {
                $newWidth = $object1->getWidth();
            }
            if ($this->height) {
                $newHeight = $this->height;
            } else {
                if (!empty($this->aspectRatio)) {
                    $newHeight = $newWidth * $this->aspectRatio;
                } else {
                    $newHeight = $object1->getHeight();
                }

            }
        }

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
        $destinationX = 0;
        if ($this->halign == 'left') {
            $destinationX = 0;
        } elseif ($this->halign == 'center') {
            $destinationX = ($newObject->getWidth() - $newWidth) / 2;
        } elseif ($this->halign == 'right') {
            $destinationX = ($newObject->getWidth() - $newWidth);
        }

        $destinationY = 0;
        if ($this->valign == 'top') {
            $destinationY = 0;
        } elseif ($this->valign == 'center') {
            $destinationY = ($newObject->getHeight() - $newHeight) / 2;
        } elseif ($this->valign == 'bottom') {
            $destinationY = ($newObject->getHeight() - $newHeight);
        }
        imagealphablending($newObject->getGDResource(), true);
        imagecopy($newObject->getGDResource(), $object1->getGDResource(), $destinationX, $destinationY, $sourceX, $sourceY, $newWidth, $newHeight);

        return $newObject;
    }
}