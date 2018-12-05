<?php namespace ImageProcess;

/**
 * Draws text on image
 * Usage example: text=ledekspert.ee,fontsize=11,font=fonts/arial.ttf,bottom=20,right=20,color=#FFF,opacity=0.5
 */
class Text extends Filter
{
    // parameters
    public $text = '';
    public $fontSize = 14;
    public $fontFile = '';
    public $top;
    public $left;
    public $bottom;
    public $right;
    public $align;
    public $color = '';
    public $opacity = 1;

    protected function processObject(ImageProcess $imageProcess, ImageObject $object1, ImageObject $object2 = null)
    {
        if (!file_exists($this->fontFile)) {
            return $object1;
        }
        $newObject = $imageProcess->getImageObjectCopy($object1);
        $image = $newObject->getGDResource();

        $text = urldecode($this->text);
        $box = imagettfbbox($this->fontSize, 0, $this->fontFile, $text);
        $boxWidth = abs($box[2] - $box[0]);
        $boxHeight = abs($box[7]);

        $imageWidth = imagesx($image);
        $imageHeight = imagesy($image);

        if ($this->align == 'center') {
            $x = $this->left + ($imageWidth - $this->left - $this->right - $boxWidth) / 2;
        } else {
            if ($this->left !== null) {
                $x = $this->left;
            } else {
                $x = $imageWidth - $this->right - $boxWidth;
            }
        }

        if ($this->top !== null) {
            $y = $this->top + $boxHeight;
        } else {
            $y = $imageHeight - $this->bottom;
        }

        $color = $this->hex2rgb($this->color);
        $alpha = 100 - 100 * $this->opacity; // eg 75 if opacity=0.25


        // draw text
        $colorIndex = imagecolorallocatealpha($image, $color[0], $color[1], $color[2], $alpha);
        imagealphablending($image, true);
        imagettftext($image, $this->fontSize, 0, $x, $y, $colorIndex, $this->fontFile, $text);

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
        return array(
            $r,
            $g,
            $b
        );
    }
}