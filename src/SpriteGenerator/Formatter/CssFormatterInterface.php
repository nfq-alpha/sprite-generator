<?php

namespace SpriteGenerator\Formatter;

interface CssFormatterInterface
{
    /**
     * Format result from source image array
     *
     * @param array $sourceImages
     * @param string $spriteClass
     * @param string $spriteImageName
     * @return string
     */
    public function format(array $sourceImages, $spriteClass, $spriteImageName);
}
