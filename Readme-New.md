# Cito Framework

---

## Table of Contents
- [Setup](#setup)
  - [Requirements](#requirements)
  - [Installation](#installation)
- [Configuration](#configuration)
- [Integrated Functions](#integrated-functions)
  - [Pages](#pages)
  - [Translations](#translations)
  - [Useragent](#-useragent)
- [Twig](#twig)
  - [Navigation](#navigtaion)
      - [Options](#options)
  - [Language Switch](#language-switch)
  - [Inline SVG](#inline-svg)
  - [Picture Macro](#picture-macro)
  - [Pages](#pages)
  - [Page Lists](#page-lists)
  - [HTML Compress](#html-compress)
- [Webpack](#webpack)

---

## Setup
### Requirements
- [PHP ^7.2](https://www.php.net/)
- [Apache](https://httpd.apache.org/)
- [Composer](https://getcomposer.org/)
- [NodeJS](https://nodejs.org/en/)
- [Yarn (optional)](https://yarnpkg.com/lang/en/)

The project can run in a virtual environment ([Docker](https://www.docker.com/), [Vagrant](https://www.vagrantup.com/), [Migraw](https://github.com/marcharding/migraw)) or directly on your machine.<br>
Composer and NPM / Yarn are required to install the PHP and JS packages.
### Installation
install via Composer
  ```bash
  composer create-project field-interactive/cito-skeleton <projectname>
  cd <projectname>
  npm install
  # or
  yarn
  ```
---

## Configuration
  If you install only the package and not the skeleton, you have to create the directories and files by hand.
  1. Create a folder `pages` in your project-root. This will be the Directory, where you store your content pages.
  1. Create a config file `cito.yaml` in `config/packages`. This file contains:
     ```yaml
     cito:
         pages: '%kernel.project_dir%/pages/'
     ```
  1. Create a routes config `z_cito.yaml` in `config/routes`. This file contains:
     ```yaml
     field_interactive_cito:
        resource: "@CitoBundle/Resources/config/routes.yaml"
      ```
  1. Add the string `'%kernel.project_dir%/pages'` to `paths` in `config/packages/twig.yaml`.
  1. Add the `picture_macro` to `filter_sets` in `config/packages/imagine.yaml`. The content of `picture_macro`:
     ```yaml
     jpeg_quality: 85
        png_compression_level: 8
            filters:
                relative_resize:
                    widen: 600`
     ```
      1. If `config/packages/imagine.yaml` is missing you can copy the file from
      `vendor/field-interactive/cito-bundle/Resources/config/packages/imagine.yaml`.

  1. If the file `config/routes/imagine.yaml` does not exist, add it and set the content:
     ```yaml
     _liip_imagine:
        resource: "@LiipImagineBundle/Resources/config/routing.yaml"
     ```

---

## Integrated Functions
### Pages
**The Pages- / Folder structure mirrors the URL**<br>

**URL:** <br>/my/awesome/page<br>

**Pages folder:** <br>
my/awesome/page.html.twig<br>
my/awesome/page/index.html.twig

### Translations
Sets global Locale if first part of the URL is the Locale:

Example:<br>
**URL:** de/my/awesome/page, **Locale:** DE

To avoid creating a folder for each language set `translation_enabled` to `true` in the `cito.yaml`.<br>
To set certain languages you need to customize the `translation_support` value in the `cito.yaml`. To add new languages set the Locale as Key and the language name as Value.

Example:
`cito.yaml`
```yaml
translation_support:
    de: Deutsch
    en: English
    es: Español
```

### Useragent
It's possible to serve different pages for different useragents. E.g. a simplified version of your page for older browsers or a message of some kind. To use this feature set `user_agent_enabled` to `true` in the `cito.yaml`.<br>
The key `default_user_agent` is to set which route is default. The key `user_agent_routing` sets which Browser get's which route.<br>
The folders used as key in `user_agent_routing` need to be created in the root of your `pages` folder. Each site goes in those folders! **The folders are not displayed in the URL**<br>

Example:
`cito.yaml`
````yaml
user_agent_enabled: true
default_user_agent: "new"
user_agent_routing:
    new: 'ie 11, opera > 52'
    old: 'ie < 11, opera < 52'
````

*Every browser except < IE 11 and Opera < 52 are routed to the new folder

Pagesfolder:
```
pages/
   new/
      new_page1/
      new_page2/
   old/
      old_page2/
      old_page2.html.twig
```

---

## Twig
### Navigtaion
Hands over a `ul`-list with Links and sets the `class="active"` to the anchor tag which matches the current URL.

Example:
`navigation.html.twig`
````html
<ul>
    <li><a href="/my/url-1">Seite 1</a></li>
    <li><a href="/my/url-2">Seite 2</a></li>
</ul>
````
`base.html.twig`
````twig
{{ navigation('path/to/navigation.html.twig') }}
````

#### Options
**Range**
````twig
{# Just Level 2 #}
{{ navigation('path/to/navigation.html.twig', {'range': 2}) }}
{# Level 2 to 4 #}
{{ navigation('path/to/navigation.html.twig', {'range': [2,4]}) }}
````

### Language Switch
Hands over a template for the language switch.<br>
Available variables
- `locale` -> current Locale
- `languages` -> an `Array` of available languages with locale as key and an array with the `link`, `language` name and `locale` as values.

The array is build from the points of `translation_support` in the `cito.yaml`.

Example:
`language-switch.html.twig`

````twig
<ul>
{% for loc, language  in languages %}
    <li>
        <a href="/{{ loc }}/{{ link }}" {% if(loc == locale) %}class="active"{% endif %}>
        {{ language }}
        </a>
    </li>
{% endfor %}
</ul>
````

`base.html.twig`
````twig
 {{ language_switch('path/to/language-switch.html.twig') }}
````

### Inline SVG
This function allows to inline the SVG in your markup. You can add attributes like classes, id's, data-attr...

Example: `base.html.twig`
Renders the raw SVG Markup
```twig
{{ asset('path/to/image.svg')|inline_svg({attr: {class: 'your-class your-second-class', id: 'svg-1'}})|raw }}
```

Renders the SVG
```twig
{{ asset('path/to/image.svg')|inline_svg({attr: {class: 'your-class your-second-class', id: 'svg-1'}})}}
```

### Picture Macro
Generates a `picture`-tag with for given sizes. The sizes need to be defined for each image. The Size-Array can have max. 6 entries.

Example: `somepage.html.twig`

````twig
{% import '@Cito/macros.html.twig' as macro %} // The Macro needs to be imported at the top of the page, on every page this is used!
{{ macro.picture('/path/to/image.jpg', [1200, 900, 600]) }}
````

### Pages
Allows to get content / attributes from other pages like:
- **blocks**: Blocks used on the page
- **link**: Link to the page without locale
- **name**: Name of the page (Filename)
- **fullPath**: Absolute Path to File
- **path**: Relative Path to File (starts at project directory)

Example: `page.html.twig`

```twig
{{ page('path/to/page.html.twig').blocks.title }} -> renders content of Block Title
```
### Page Lists
Loads a directory or array of file paths as array of page objects. The page, this function is used on, **is not** shown.

Example: `somepage.html.twig`

```twig
{% set pagelist = pagelist({'dir': 'path/to/dir'}) %}
{% for page in pagelist %}
<a href="{{ page.link }}">{{ page.name }}</a>
{% endfor %}
```

### HTML Compress
The ability to compress the output html

Example: `base.html.twig`

```twig
{% htmlcompress %}
<p>
    Hello World
</p>
{% endhtmlcompress %}
```

Returns

```html
<p>Hello World</p>
```

other ways to call `htmlcompress`:

```twig
{{ 'My Text'|htmlcompress }}
{{ htmlcompress('My Text') }}
```
GitHub: https://github.com/nochso/html-compress-twig

---

## Webpack
Comming Soon
