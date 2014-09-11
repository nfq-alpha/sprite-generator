<?php

namespace SpriteGenerator\ImageGenerator;

use SpriteGenerator\Exception\SpriteException;
use SpriteGenerator\Positioner\SpritePositionerInterface;

class Gd2Generator implements ImageGeneratorInterface
{
    /**
     * @param array $sourceImages
     * @param \SpriteGenerator\Positioner\SpritePositionerInterface $resultImage
     * @param SpritePositionerInterface $positioner
     * @throws \SpriteGenerator\Exception\SpriteException
     * @return string
     *
     * @TODO: move formatting to twig template
     */
    public function generate(array $sourceImages, $resultImage, SpritePositionerInterface $positioner)
    {

        $width = $positioner->getSpriteImageWidth();
        $height = $positioner->getSpriteImageHeight();

        $im = imagecreatetruecolor($width, $height);

        foreach ($sourceImages as &$image) {
            switch ($image['mime']) {
                case "image/gif":
                    $tmp = imagecreatefromgif($image['file']);
                    break;
                case "image/jpeg":
                    $tmp = imagecreatefromjpeg($image['file']);
                    break;
                case "image/png":
                    $tmp = imagecreatefrompng($image['file']);
                    break;
                case "image/bmp":
                    throw new SpriteException('BMP format is not supported');
                    break;
            }

            imagecopyresampled(
                $im,
                $tmp,
                $image['pos_x'],
                $image['pos_y'],
                0,
                0,
                $image['width'],
                $image['height'],
                $image['width'],
                $image['height']
            );
        }

        // TODO: check if saving worked
        imagepng($im, $resultImage);

        return true;
    }
}
