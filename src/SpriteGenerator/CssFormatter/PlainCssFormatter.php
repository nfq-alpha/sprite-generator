<?php

namespace SpriteGenerator\CssFormatter;

class PlainCssFormatter extends BaseFormatter implements CssFormatterInterface
{
    /**
     * @var string
     */
    protected $template = 'plainCss.html.twig';
}
