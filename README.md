# Cito Framework

---

## Setup
### Installation
- clone the completed project from Gitlab
    - `git clone https://gitlab.com/myboom/cito-symfony.git`
- or create a new project with composer
    - `composer create-project field-interactive/cito-skeleton`

To install the PHP and JS packages you need Composer and Yarn.

### Configuration
If you install only the package and not the skeleton, you have to create the directories and files by hand.
1. Create a folder `pages` in your project-root. This will be the Directory, where you store your content pages.
1. Create a config file `cito.yaml` in `config/packages`. This file contains:

        cito:
            pages: '%kernel.project_dir%/pages/'

1. Create a routes config `z_cito.yaml` in `config/routes`. This file contains:

        field_interactive_cito:
            resource: "@CitoBundle/Resources/config/routes.yaml"
    
1. Add the string `'%kernel.project_dir%/pages'` to `paths` in `config/packages/twig.yaml`.
1. Add the `picture_macro` to `filter_sets` in `config/packages/imagine.yaml`. The content of `picture_macro`:
    
        jpeg_quality: 85
            png_compression_level: 8
            filters:            
                relative_resize:
                    widen: 600

    1. If `config/packages/imagine.yaml` is missing you can copy the file from 
    `vendor/field-interactive/cito-bundle/Resources/config/packages/imagine.yaml`.

1. If the file `config/routes/imagine.yaml` does not exist, add it and set the content:

        _liip_imagine:
            resource: "@LiipImagineBundle/Resources/config/routing.yaml"

---

## Twig Functions, Filters and Macros
### CitoController
Search the page to the current route, if you did not define the route. The controller is searching
for the page in the `pages` folder in your project-root. 

Example: `/example/page`

The controller search for `%project_dir%/pages/example/page.html.twig` and 
`%project_dir%/pages/example/page/index.html.twig`.

### Navigation
Adds an 'active' class to the current link item in the navigation list. The navigation needs to be a simple `<ul> <li>` list,
with an `<a>` tag inside.

    {{ navigation('path/to/nav.html') }}

You can add a parameters array:
- breadcrumbs (boolean): generate breadcrumbs to the current route
    - `{{ navigation('path/to/nav.html', {'breadcrumbs': true}) }}`
- range (int/array): generate the navigation from a level
    - `{{ navigation('path/to/nav.html', {'range': 2}) }}`
    - `{{ navigation('path/to/nav.html', {'range': [2,4]}) }}`

### Page
Loads a page object to get its blocks, link, path or name.

    {% set page = page('path/to/page.html.twig') %}

You can access the attributes like an object:
- `{{ page('path/to/page.html.twig').blocks.title }}`

### Pagelist
Loads a directory or set of pages as a list of [page objects](#page).

    {% set pagelist = pagelist({'dir': 'path/to/dir'}) %}
    {% set pagelist = pagelist({'files': [
        'path/to/file_1.html.twig',
        'path/to/file_2.html.twig',
        'path/to/file_1.htm3.twig'
        ]}) %}

You can iterate over the list and access every page:
- `{% for page in pagelist %}`

You can sort and limit the result:
- `{% set pagelist = pagelist({'dir': 'path/to/dir', 'sortOrder': 'desc|asc', 'sortBy': 'link', 'limit': 10}) %}`

### Picture
Creates an html picture tag for an image with a set of given sizes.

    {% import '@Cito/macros.html.twig' as macro %}
    {{ macro.picture('/path/to/image.jpg', [1200, 900, 600]) }}

You can set up to 6 sizes.

### SocialMedia
Loads new posts from a SocialMedia-Platform (like Facebook, Twitter or Instagram)

    YAML:
    social_media:
        facebook:
            yourFacebookPage:
                pageId: ''
                accessToken: ''

### InlineSVG
Adds or replaces attributes to an svg image.

    {{ asset('path/to/image.svg')|inline_svg({attr: {class: 'your-class', id: 'svg-1'}})|raw }}

You can also add some classes:
- `{{ asset('path/to/image.svg')|inline_svg({classes: 'add-classname another-classname'})|raw }}`

### Body class and id
Set the class and the id of the `<body>` tag.

    {% set body_class = 'your-class' %}
    {% set body_id = 'your-id' %}
    
    
### IncludeUserAgent
Includes a template with user agent checks

There are two usages:

Replace 'template file' with the file you want to include.
The file will be searched for in the pages directory set in cito.yml.
Setting no folder will automatically choose it based on the User Agent.
If the folder is set, it will take the include the file from there.

    {{ include_ua('template file') }}
    or
    {{ include_ua('template file', 'folder') }}



---

## JavaScripts and Webpack
### Lazyload
[vanilla lazyload](https://github.com/verlok/lazyload)

### Sass compiling
To write better and cleaner css you can use sass, which you can compile over `npm build`.
You only need the `assets/sass/default.sass` where you combine your sass files.

### Watcher
Auto compiles sass and minifys css and js. You can start a watcher with the command `npm watch`.
If you want to auto refresh the browser after new compiling you can use the browser sync `gulp serve`.

---

## Useful documentations
- [Symfony](https://symfony.com/doc/current/index.html)
- [Twig](https://twig.symfony.com/doc/2.x/)


## About Us
We are [Field-Interactive](https://www.field-interactive.com)
