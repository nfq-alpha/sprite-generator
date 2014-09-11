<?php

namespace SpriteGenerator\Positioner;

interface SpritePositionerInterface
{
    /**
     * Calculate image positions in image image
     *
     * @param array $sourceImages
     * @param int $padding
     * @return array
     */
    public function calculate(array $sourceImages, $padding);

    /**
     * Get result image width
     *
     * @return int
     */
    public function getSpriteImageWidth();

    /**
     * Get result image height
     *
     * @return int
     */
    public function getSpriteImageHeight();
}
