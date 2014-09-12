<?php

namespace SpriteGenerator\Positioner;

class OneColumnPositioner implements SpritePositionerInterface
{
    /**
     * @var int
     */
    private $spriteWidth = 0;

    /**
     * @var int
     */
    private $spriteHeight = 0;

    /**
     * @param array $sourceImages
     * @param int $padding
     * @return array
     */
    public function calculate(array $sourceImages, $padding)
    {
        $width = $height = 0;

        foreach ($sourceImages as &$image) {
            $image['filesize'] = filesize($image['file']);

            $imgInfo = getimagesize($image['file']);

            $image['width'] = $imgInfo[0];
            $image['height'] = $imgInfo[1];
            $image['mime'] = $imgInfo['mime'];

            $image['pos_x'] = 0;
            $image['pos_y'] = $height;

            $height += $image['height'] + $padding;

            if ($image['width'] > $width) {
                $width = $image['width'];
            }
        }

        $this->spriteWidth = $width;
        $this->spriteHeight = $height - $padding;

        return $sourceImages;
    }

    /**
     * @return int
     */
    public function getSpriteImageWidth()
    {
        return $this->spriteWidth;
    }

    /**
     * @return int
     */
    public function getSpriteImageHeight()
    {
        return $this->spriteHeight;
    }
}
