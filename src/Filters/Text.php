<?php
declare(strict_types=1);

namespace ImageProcess\Filters;

use ImageProcess\Filter;
use ImageProcess\ImageObject;
use ImageProcess\ImageProcess;

/**
 * Draws text on image
 * Usage example: text=ledekspert.ee,fontsize=11,font=fonts/arial.ttf,bottom=20,right=20,color=#FFF,opacity=0.5
 */
class Text extends Filter
{
    // parameters
    public ?string $text = '';
    public ?int $fontSize = 14;
    public ?string $fontFile = '';
    public ?int $top;
    public ?int $left;
    public ?int $bottom;
    public ?int $right;
    public ?string $align;
    public ?string $color = '';
    public ?int $opacity = 1;

    protected function processObject(ImageProcess $imageProcess, ImageObject $object1, ?ImageObject $object2 = null): ?ImageObject
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

        if ($this->align === 'center') {
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

    protected function hex2rgb($hex): array
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
        return [
            $r,
            $g,
            $b,
        ];
    }
}