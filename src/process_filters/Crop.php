<?php namespace ImageProcess;

class Crop extends Filter
{
    public $soft;
    public $width;
    public $height;
    public $color;
    public $valign = 'center';
    public $halign = 'center';

    protected function processObject(ImageProcess $imageProcess, ImageObject $object1, ImageObject $object2 = null)
    {
        //if image was smaller than width or height and soft mode is used,
        //then we still have to cut some of it to retain a requested aspect ration
        if ($this->soft && ($this->width > $object1->width || $this->height > $object1->height)) {
            $requestedAspect = $this->width / $this->height;
            if ($this->width > $object1->width) {
                $newWidth = $object1->width;
                $newHeight = $newWidth / $requestedAspect;

                if ($newHeight > $object1->height) {
                    $newHeight = $object1->height;
                    $newWidth = $newHeight * $requestedAspect;
                }
            } elseif ($this->height > $object1->height) {
                $newHeight = $object1->height;
                $newWidth = $newHeight * $requestedAspect;

                if ($newWidth > $object1->width) {
                    $newWidth = $object1->width;
                    $newHeight = $newWidth / $requestedAspect;
                }
            }


        } else {
            if (!$this->width) {
                $newWidth = $object1->width;
            } else {
                $newWidth = $this->width;
            }
            if (!$this->height) {
                $newHeight = $object1->height;
            } else {
                $newHeight = $this->height;
            }
        }

        $newObject = $imageProcess->getEmptyImageObject($newWidth, $newHeight);
        if ($this->color) {
            $color = hexdec($this->color);
        } else {
            $color = imagecolorallocatealpha($newObject->GDResource, 0, 0, 0, 127);
        }
        imagefilledrectangle($newObject->GDResource, 0, 0, $newWidth, $newHeight, $color);

        $sourceX = 0;
        if ($object1->width > $newWidth) {
            $sourceX = ($object1->width - $newWidth) / 2;
        }

        $sourceY = 0;
        if ($object1->height > $newHeight) {
            $sourceY = ($object1->height - $newHeight) / 2;
        }

        if ($object1->width < $newWidth) {
            $newWidth = $object1->width;
        }
        if ($object1->height < $newHeight) {
            $newHeight = $object1->height;
        }
        $destinationX = 0;
        if ($this->halign == 'left') {
            $destinationX = 0;
        } elseif ($this->halign == 'center') {
            $destinationX = ($newObject->width - $newWidth) / 2;
        } elseif ($this->halign == 'right') {
            $destinationX = ($newObject->width - $newWidth);
        }

        $destinationY = 0;
        if ($this->valign == 'top') {
            $destinationY = 0;
        } elseif ($this->valign == 'center') {
            $destinationY = ($newObject->height - $newHeight) / 2;
        } elseif ($this->valign == 'bottom') {
            $destinationY = ($newObject->height - $newHeight);
        }
        imagealphablending($newObject->GDResource, true);
        imagecopy($newObject->GDResource, $object1->GDResource, $destinationX, $destinationY, $sourceX, $sourceY, $newWidth, $newHeight);

        return $newObject;
    }
}