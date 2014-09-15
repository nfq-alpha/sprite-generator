<?php

namespace SpriteGenerator\Services;

use SpriteGenerator\Exception\SpriteException;
use SpriteGenerator\CssFormatter\PlainCssFormatter;
use SpriteGenerator\CssFormatter\SassFormatter;
use SpriteGenerator\Positioner\OneColumnPositioner;
use SpriteGenerator\Positioner\MinImageSizePositioner;
use SpriteGenerator\ImageGenerator\Gd2Generator;


/**
 * Sprite generator service
 *
 * @TODO: check if method visibility is correct
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
    protected function getSpriteList($spriteName)
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
    protected function create()
    {
        $images = $this->getSpriteSourceImages();

        $this->createSpriteImage($images);
        $this->createSpriteCss($images);

        return true;
    }

    /**
     * @return array
     */
    protected function getSpriteSourceImages()
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

        asort($images);

        return $images;
    }

    /**
     * @param $images
     * @throws \SpriteGenerator\Exception\SpriteException
     * @return bool
     */
    protected function createSpriteImage(&$images)
    {
        $padding = $this->getConfigParam('padding');
        $resultImage = $this->getConfigParam('outImage');

        $positioner = $this->getSpritePositioner();
        $generator = $this->getImageGenerator();

        $images = $positioner->calculate($images, $padding);
        return $generator->generate($images, $resultImage, $positioner);
    }

    /**
     * @return SpritePositionerInterface
     */
    protected function getSpritePositioner()
    {
        switch ($this->getConfigParam('imagePositioning')) {
            case 'one-column':
                $positioner = new OneColumnPositioner();
                break;
            case 'min-image':
                $positioner = new MinImageSizePositioner();
                break;
        }

        return $positioner;
    }

    /**
     * @return ImageGeneratorInterface
     */
    protected function getImageGenerator()
    {
        switch ($this->getConfigParam('imageGenerator')) {
            case 'gd2':
                $generator = new Gd2Generator();
                break;
        }

        return $generator;
    }

    /**
     * @param $images
     * @throws \SpriteGenerator\Exception\SpriteException
     * @return bool
     */
    protected function createSpriteCss($images)
    {
        $formatter = $this->getCssFormatter();
        $spriteClass = $this->getConfigParam('spriteClass');
        $spriteImageName = $this->getRelativeSpriteImageUrl($images);
        $formattedCss = $formatter->format($images, $spriteClass, $spriteImageName);

        $resultCss = $this->getConfigParam('outCss');

        $saved = file_put_contents($resultCss, $formattedCss);
        if ($saved === false) {
            throw new SpriteException('Saving CSS failed. Maybe "'.$resultCss.'" does not have write permissions?');
        }

        return true;
    }

    /**
     * @param $images
     * @return string
     */
    protected function getRelativeSpriteImageUrl($images)
    {
        $imageHash = substr(md5(serialize($images)), 10, 20);

        $spriteImageName = $this->getConfigParam('relativeImagePath');
        $spriteImageName .= basename($this->getConfigParam('outImage'));
        $spriteImageName .= '?' . $imageHash;

        return $spriteImageName;
    }

    /**
     * @return CssFormatterInterface
     */
    protected function getCssFormatter()
    {
        switch ($this->getConfigParam('cssFormat')) {
            case 'css':
                $formatter = new PlainCssFormatter();
                break;
            case 'sass':
                $formatter = new SassFormatter();
                break;
        }

        return $formatter;
    }
}
