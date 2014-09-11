<?php

namespace SpriteGenerator\Services;

use SpriteGenerator\Exception\DirectoryException;
use SpriteGenerator\Exception\SpriteException;


/**
 * Sprite generator base class
 */
class SpriteService
{

    private $options;
    private $cssMap = array();

    /**
     * @var SpriteConfInterface
     */
    private $config;

    /*
     * set config for command line
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /*
     * get config for command line
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Sprite List
     *
     */
    public function getSpriteList()
    {
        $spriteList = $this->getConfig();
        if (empty($spriteList)) {
            $spriteList = null;
        }
        return $spriteList;
    }


    /**
     * generate sprite
     *
     */
    public function generateSprite($spriteName = false)
    {
        $spriteList = $this->getSpriteList();

        if ($spriteName) {
            if (!isset($spriteList[$spriteName])) {
                throw new DirectoryException('Sprite config for ' . $spriteName . ' not found');
            }

            $spriteList = array($spriteName => $spriteList[$spriteName]);
        }

        foreach ($spriteList as $spriteInfo) {
            if (file_exists($spriteInfo['inDir']) === false) {
                throw new DirectoryException('Image source directory doesn\'t exist');
            }

            try {
                $this->setOptions($spriteInfo);
                $this->create();
            } catch (SpriteException $de) {
                throw new SpriteException('Sprite cannot be generate');
            }
        }

        return true;
    }

    public function setOptions($spriteInfo)
    {
        $this->options = $spriteInfo;
    }

    public function getOption($name)
    {
        if (!isset($this->options[$name])) {
            throw new SpriteException('Sprite option "' . $name . '" is not set');
        }
        return $this->options[$name];
    }

    public function createSpriteCss()
    {
        $spriteImages = $this->getCssMap();
        $imageHash = substr(md5(serialize($spriteImages)), 10, 20);

        $imageName = $this->getOption('relativeImagePath');
        $imageName .= basename($this->getOption('outImage'));

        $css = '';
        $this->addCssLine($css, ".{$this->getOption('spriteClass')} {");
        $this->addCssLine($css, "\tbackground: url({$imageName}?{$imageHash}) no-repeat;");
        $this->addCssLine($css, "}");
        $this->addCssLine($css, "");

        foreach ($spriteImages as $key => $image) {
            $this->addCssLine($css, ".{$key} {");

            $this->addCssLine($css, "\twidth: {$image['width']}px;");
            $this->addCssLine($css, "\theight: {$image['height']}px;");

            $this->addCssLine($css, "\tbackground-position: -{$image['pos_x']}px -{$image['pos_y']}px;");
            $this->addCssLine($css, "}");

            $this->addCssLine($css, "");
        }

        // TODO: check if saving worked
        file_put_contents($this->getOption('outCss'), $css);

        return true;
    }

    public function addCssLine(&$wholeCss, $addCss)
    {
        $wholeCss = $wholeCss . "$addCss\r\n";
    }

    public function create()
    {
        $sourceDir = $this->getOption('inDir');
        $dh = opendir($sourceDir);
        $images = array();
        while (false !== ($filename = readdir($dh))) {
            if (!is_file($sourceDir . $filename)) {
                continue;
            }
            $fileCode = substr($filename, 0, strrpos($filename, '.'));
            $images[$fileCode]['file'] = $sourceDir . $filename;
        }

        $this->createSpriteImage($images);
        $this->createSpriteCss();

        return true;
    }


    public function createSpriteImage($images)
    {
        $images = $this->mergeImages($images);
        $this->setCssMap($images);
    }


    public function mergeImages($images)
    {
        // $this->getSpacing()

        $imgInfo = array();
        $len = count($images);
        $wc = ceil(sqrt($len));
        $hc = floor(sqrt($len / 2));
        $maxW = array();
        $maxH = array();

        // TODO: padding

        $i = 0;
        foreach ($images as &$image) {
            $imgInfo[$i] = getimagesize($image['file']);
            $image['width'] = $imgInfo[$i][0];
            $image['height'] = $imgInfo[$i][1];
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

        $height = 0;
        for ($i = 0; $i < $hc; $i++) {
            $height += $imgInfo[$maxH[$i]][1];
        }

//        echo __FILE__.': '.__LINE__.'<pre>';var_dump($wc, $hc, $width, $height, $maxW, $maxH);echo '</pre>'; // TODO: REMOVE
//        exit;
        $im = imagecreatetruecolor($width, $height);

        $wCnt = 0;
        $startWFrom = 0;
        $startHFrom = 0;
        $i = 0;
        foreach ($images as &$image) {
            switch ($imgInfo[$i]['mime']) {
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
            $image['pos_x'] = $startWFrom;
            $image['pos_y'] = $startHFrom;
            imagecopyresampled(
                $im,
                $tmp,
                $startWFrom,
                $startHFrom,
                0,
                0,
                $imgInfo[$i][0],
                $imgInfo[$i][1],
                $imgInfo[$i][0],
                $imgInfo[$i][1]
            );
            $wCnt++;
            if ($wCnt == $wc) {
                $startWFrom = 0;
                $startHFrom += $imgInfo[$maxH[0]][1];
                $wCnt = 0;
            } else {
                $startWFrom += $imgInfo[$i][0];
            }
            $i++;
        }

        // TODO: check if saving worked
        imagepng($im, $this->getOption('outImage'));

        return $images;
    }

    public function setCssMap($v)
    {
        $this->cssMap = $v;
        return $this;
    }

    public function getCssMap()
    {
        return $this->cssMap;
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