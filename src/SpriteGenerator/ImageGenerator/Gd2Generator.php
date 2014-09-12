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
     */
    public function generate(array $sourceImages, $resultImage, SpritePositionerInterface $positioner)
    {
        $width = $positioner->getSpriteImageWidth();
        $height = $positioner->getSpriteImageHeight();

        $im = imagecreatetruecolor($width, $height);

        $im = $this->addTransparencyToImage($im);

        foreach ($sourceImages as &$image) {
            $tmp = $this->createImageFromFile($image);

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

        return $this->saveResultImage($im, $resultImage);
    }

    /**
     * Add transparency & transparent background
     *
     * @param $im
     * @return mixed
     */
    private function addTransparencyToImage($im)
    {
        imagealphablending($im, false);
        $transparency = imagecolorallocatealpha($im, 0, 0, 0, 127);
        imagefill($im, 0, 0, $transparency);
        imagesavealpha($im, true);

        return $im;
    }

    /**
     * @param $image
     * @return resource
     * @throws \SpriteGenerator\Exception\SpriteException
     */
    private function createImageFromFile($image)
    {
        $file = $image['file'];
        $format = $image['mime'];

        switch ($format) {
            case "image/gif":
                $tmp = imagecreatefromgif($file);
                break;
            case "image/jpeg":
                $tmp = imagecreatefromjpeg($file);
                break;
            case "image/png":
                $tmp = imagecreatefrompng($file);
                break;
            default:
                throw new SpriteException('Image format "' . $format . '" (file "' . $file . '") is not supported.');
        }

        return $tmp;
    }

    /**
     * @param $im
     * @param $resultImage
     * @return bool
     * @throws \SpriteGenerator\Exception\SpriteException
     */
    private function saveResultImage($im, $resultImage)
    {
        $format = pathinfo($resultImage, PATHINFO_EXTENSION);

        switch ($format) {
            case "gif":
                $saved = imagegif($im, $resultImage);
                break;
            case "jpg":
            case "jpeg":
                $saved = imagejpeg($im, $resultImage, 89);
                break;
            case "png":
                $saved = imagepng($im, $resultImage);
                break;
            default:
                throw new SpriteException('Result image format "' . $format . '" is not supported.');
        }

        if ($saved === false) {
            throw new SpriteException('Saving image failed. Maybe "' . $resultImage . '" does not have write permissions?');
        }

        return true;
    }
}
