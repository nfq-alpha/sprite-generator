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
     * @return array
     */
    public function calculate(array $sourceImages)
    {
        $imgInfo = array();
        $len = count($sourceImages);
        $wc = ceil(sqrt($len));
        $hc = floor(sqrt($len / 2));
        $maxW = array();
        $maxH = array();

        // TODO: add padding -> $this->getSpacing()

        $i = 0;
        foreach ($sourceImages as &$image) {
            $imgInfo[$i] = getimagesize($image['file']);
            $image['width'] = $imgInfo[$i][0];
            $image['height'] = $imgInfo[$i][1];
            $image['mime'] = $imgInfo[$i]['mime'];
            $found = false;
            for ($j = 0; $j < $i; $j++) {
                if ($imgInfo[$maxW[$j]][0] < $imgInfo[$i][0]) {
                    $farr = $j > 0 ? array_slice($maxW, $j - 1, $i) : array();
                    $maxW = array_merge($farr, array($i), array_slice($maxW, $j));
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $maxW[$i] = $i;
            }
            $found = false;
            for ($j = 0; $j < $i; $j++) {
                if ($imgInfo[$maxH[$j]][1] < $imgInfo[$i][1]) {
                    $farr = $j > 0 ? array_slice($maxH, $j - 1, $i) : array();
                    $maxH = array_merge($farr, array($i), array_slice($maxH, $j));
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $maxH[$j] = $j;
            }
            $i++;
        }

        $width = 0;
        for ($i = 0; $i < $wc; $i++) {
            $width += $imgInfo[$maxW[$i]][0];
        }
        $this->spriteWidth = $width;

        $height = 0;
        for ($i = 0; $i < $hc; $i++) {
            $height += $imgInfo[$maxH[$i]][1];
        }
        $this->spriteHeight = $height;

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
