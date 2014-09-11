<?php

namespace SpriteGenerator\ImageGenerator;

use SpriteGenerator\Positioner\SpritePositionerInterface;

interface ImageGeneratorInterface
{
    /**
     * Format result from source image array
     *
     * @param array $sourceImages
     * @param $resultImage
     * @param SpritePositionerInterface $positioner
     * @return string
     */
    public function generate(array $sourceImages, $resultImage, SpritePositionerInterface $positioner);
}
