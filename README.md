Nfq Sprite Generator Bundle
==================
Easily generate sprites in your Symfony2 environment without any dependencies


## Installation
### Dependancies

### Get the bundle

Add this in your composer.json

```json
{
	"require": {
		"nfq-alpha/sprite-generator": "dev-master@dev"
	}
}
```

and then run

```sh
php composer.phar update
```

### Add the classes to your Kernel
```php
new SpriteGenerator\SpriteGeneratorBundle(),
```

### Configuration
You have to configure your sprite by puting the following lines in your ```config.yml``` file: 

Minimal configuration:
```yaml
nfq_sprite_generator:
    sprite:
        spritename:
            inDir: %kernel.root_dir%/../src/Resources/public/img/sprites/
            outImage: %kernel.root_dir%/../src/Resources/public/img/sprite2.png
            outCss: %kernel.root_dir%/../src/Resources/public/scss/_sprites2.scss
            relativeImagePath: ../bundles/img/
            padding: 5
            spriteClass: sprite
        another_spritename:
            inDir: %kernel.root_dir%/../src/Resources/public/img/sprites/
            outImage: %kernel.root_dir%/../src/Resources/public/img/sprite2.png
            outCss: %kernel.root_dir%/../src/Resources/public/scss/_sprites2.scss
            relativeImagePath: ../bundles/img/
            padding: 5
            spriteClass: sprite_another
```

## Generate sprites
Generate all your sprites : 
```sh
$ php app/console nfq:sprite:generate
```

Generate one sprite : 
```sh
$ php app/console nfq:sprite:generate spritename
```

## Use in your templates
You have now to integrade your generated files in your templates.
Example : 
```twig
{% block stylesheets %}
    {% stylesheets
        "img/sprite/*.css"
        output="css/sprite.css"
    %}
        <link rel="stylesheet" type="text/css" href="{{ asset_url }}" />
    {% endstylesheets %}
{% endblock %}
```
