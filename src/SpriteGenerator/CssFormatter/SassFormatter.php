<?php

namespace SpriteGenerator\CssFormatter;

class SassFormatter extends BaseFormatter implements CssFormatterInterface
{
    /**
     * @var string
     */
    protected $template = 'sass.html.twig';
}
