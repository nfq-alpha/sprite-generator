<?php

namespace SpriteGenerator\Services;

use SpriteGenerator\Exception\SpriteException;
use SpriteGenerator\Formatter\SassFormatter;
use SpriteGenerator\Positioner\OneColumnPositioner;


/**
 * Sprite generator service
 */
class SpriteService
{
    /**
     * All sprite configs
     * @var
     */
    private $config = array();

    /**
     * Active sprite name
     * @var string
     */
    private $activeSprite = null;

    private $cssMap = array();

    /**
     * @param $config array
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param $name string
     * @return string
     * @throws \SpriteGenerator\Exception\SpriteException
     */
    public function getConfigParam($name)
    {
        if (!isset($this->config[$this->activeSprite][$name])) {
            throw new SpriteException('Sprite config "' . $name . '" is not set.');
        }

        return $this->config[$this->activeSprite][$name];
    }

    /**
     * @param $spriteName
     */
    public function setActiveSprite($spriteName)
    {
        $this->activeSprite = $spriteName;
    }

    /**
     * @param $spriteName string
     * @return array
     * @throws \SpriteGenerator\Exception\SpriteException
     */
    public function getSpriteList($spriteName)
    {
        $spriteList = $this->getConfig();
        if (empty($spriteList)) {
            throw new SpriteException('No sprite configs found');
        }

        if ($spriteName) {
            if (!isset($spriteList[$spriteName])) {
                throw new SpriteException('Sprite config for ' . $spriteName . ' not found');
            }

            $spriteList = array($spriteName => $spriteList[$spriteName]);
        }

        return $spriteList;
    }

    /**
     * @param bool $spriteName
     * @return bool
     * @throws \SpriteGenerator\Exception\SpriteException
     */
    public function generateSprite($spriteName = false)
    {
        $spriteList = $this->getSpriteList($spriteName);

        foreach ($spriteList as $spriteName => $spriteInfo) {
            if (file_exists($spriteInfo['inDir']) === false) {
                throw new SpriteException('Image source directory doesn\'t exist');
            }

            $this->setActiveSprite($spriteName);
            $this->create();
        }

        return true;
    }

    /**
     * @return bool
     */
    public function create()
    {
        $images = $this->getSpriteSourceImages();

        $this->createSpriteImage($images);
        $this->createSpriteCss($images);

        return true;
    }

    /**
     * @return array
     */
    public function getSpriteSourceImages()
    {
        $sourceDir = $this->getConfigParam('inDir');
        $dh = opendir($sourceDir);

        $images = array();
        while (false !== ($filename = readdir($dh))) {
            if (!is_file($sourceDir . $filename)) {
                continue;
            }
            $fileCode = substr($filename, 0, strrpos($filename, '.'));
            $images[$fileCode]['file'] = $sourceDir . $filename;
        }

        return $images;
    }

    /**
     * @param $images
     * @throws \SpriteGenerator\Exception\SpriteException
     * @return bool
     * @TODO: split to: image position getters, generators configurable with config
     */
    public function createSpriteImage(&$images)
    {
        switch ($this->getConfigParam('imagePositioning')) {
            case 'one-column':
                $positioner = new OneColumnPositioner();
                break;
        }

        $padding = $this->getConfigParam('padding');

        $images = $positioner->calculate($images, $padding);
        $width = $positioner->getSpriteImageWidth();
        $height = $positioner->getSpriteImageHeight();

        $im = imagecreatetruecolor($width, $height);

        foreach ($images as &$image) {
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
        imagepng($im, $this->getConfigParam('outImage'));

        return true;
    }

    /**
     * @param $images
     * @return bool
     */
    public function createSpriteCss($images)
    {
        $spriteClass = $this->getConfigParam('spriteClass');

        $imageHash = substr(md5(serialize($images)), 10, 20);

        $spriteImageName = $this->getConfigParam('relativeImagePath');
        $spriteImageName .= basename($this->getConfigParam('outImage'));
        $spriteImageName .= '?' . $imageHash;

        switch ($this->getConfigParam('cssFormat')) {
            case 'sass':
                $formatter = new SassFormatter();
                break;
        }
        $formattedCss = $formatter->format($images, $spriteClass, $spriteImageName);

        // TODO: check if saving worked
        file_put_contents($this->getConfigParam('outCss'), $formattedCss);

        return true;
    }
}









/*
function mergeImages($images) {
    $imageData = array();
    $len = count($images);
    $wc = ceil(sqrt($len));
    $hc = floor(sqrt($len/2));
    $maxW = array();
    $maxH = array();
    for($i = 0; $i < $len; $i++) {
        $imageData[$i] = getimagesize($images[$i]);
        $found = false;
        for($j = 0; $j < $i; $j++) {
            if ( $imageData[$maxW[$j]][0] < $imageData[$i][0] ) {
                $farr = $j > 0 ? array_slice($maxW, $j-1, $i) : array();
                $maxW = array_merge($farr, array($i), array_slice($maxW, $j));
                $found = true;
                break;
            }
        }
        if ( !$found ) {
            $maxW[$i] = $i;
        }
        $found = false;
        for($j = 0; $j < $i; $j++) {
            if ( $imageData[$maxH[$j]][1] < $imageData[$i][1] ) {
                $farr = $j > 0 ? array_slice($maxH, $j-1, $i) : array();
                $maxH = array_merge($farr, array($i), array_slice($maxH, $j));
                $found = true;
                break;
            }
        }
        if ( !$found ) {
            $maxH[$i] = $i;
        }
    }

    $width = 0;
    for($i = 0; $i < $wc; $i++) {
        $width += $imageData[$maxW[$i]][0];
    }

    $height = 0;
    for($i = 0; $i < $hc; $i++) {
        $height += $imageData[$maxH[$i]][1];
    }

    $im = imagecreatetruecolor($width, $height);

    $wCnt = 0;
    $startWFrom = 0;
    $startHFrom = 0;
    for( $i = 0; $i < $len; $i++ ) {
        $tmp = imagecreatefromjpeg($images[$i]);
        imagecopyresampled($im, $tmp, $startWFrom, $startHFrom, 0, 0, $imageData[$i][0], $imageData[$i][1], $imageData[$i][0], $imageData[$i][1]);
        $wCnt++;
        if ( $wCnt == $wc ) {
            $startWFrom = 0;
            $startHFrom += $imageData[$maxH[0]][1];
            $wCnt = 0;
        } else {
            $startWFrom += $imageData[$i][0];
        }
    }


    return $im;
}*/