<?php

namespace SpriteGenerator\CssFormatter;

use Twig;

abstract class BaseFormatter
{
    /**
     * @param array $sourceImages
     * @param string $spriteClass
     * @param string $spriteImageName
     * @return string
     */
    public function format(array $sourceImages, $spriteClass, $spriteImageName)
    {
        return $this->formatTemplate($sourceImages, $spriteClass, $spriteImageName);
    }

    /**
     * @param array $sourceImages
     * @param string $spriteClass
     * @param string $spriteImageName
     * @return string
     */
    public function formatTemplate(array $sourceImages, $spriteClass, $spriteImageName)
    {
        $loader = new \Twig_Loader_Filesystem(dirname(__FILE__).'/../Resources/views');
        $twig = new \Twig_Environment($loader, array(
            'cache' => false,
            'debug' => true,
        ));
        $twig->addExtension(new \Twig_Extension_Debug());

        $css = $twig->render($this->template,
            array(
                'spriteClass' => $spriteClass,
                'spriteImageName' => $spriteImageName,
                'images' => $sourceImages,
            )
        );

        return $css;
    }
}
