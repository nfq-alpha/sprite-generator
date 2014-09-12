<?php

namespace SpriteGenerator\CssFormatter;

class PlainCssFormatter implements CssFormatterInterface
{
    /**
     * @param array $sourceImages
     * @param string $spriteClass
     * @param string $spriteImageName
     * @return string
     *
     * @TODO: move formatting to twig template
     */
    public function format(array $sourceImages, $spriteClass, $spriteImageName)
    {
        $css = '';
        $this->addCssLine($css, ".{$spriteClass} {");
        $this->addCssLine($css, "\tbackground: url({$spriteImageName}) no-repeat;");
        $this->addCssLine($css, "}");
        $this->addCssLine($css, "");

        foreach ($sourceImages as $key => $image) {
            $this->addCssLine($css, ".{$key} {");

            $this->addCssLine($css, "\twidth: {$image['width']}px;");
            $this->addCssLine($css, "\theight: {$image['height']}px;");

            $this->addCssLine($css, "\tbackground-position: -{$image['pos_x']}px -{$image['pos_y']}px;");
            $this->addCssLine($css, "}");

            $this->addCssLine($css, "");
        }

        return $css;
    }

    /**
     * @param $wholeCss string
     * @param $addCss string
     */
    public function addCssLine(&$wholeCss, $addCss)
    {
        $wholeCss = $wholeCss . "$addCss\r\n";
    }
}
