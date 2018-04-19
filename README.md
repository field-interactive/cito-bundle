# Cito Framework

---

## Installation
- clone the completed project from Gitlab
    - `git clone https://gitlab.com/myboom/cito-symfony.git`
- or create a new project with composer
    - `composer create-project field-interactive/cito-skeleton -s dev`

To install the PHP and JS packages you need Composer and Yarn.

---

## Twig Functions, Filters and Macros
### Navigation
Adds an 'active' class to the current link item in the navigation list. The navigation needs to be a simple `<ul>` list,
with an `<a>` tag inside.

    {{ navigation('path/to/nav.html') }}

You can add a parameters array:
- breadcrumbs (boolean): generate breadcrumbs to the current route
    - `{{ navigation('path/to/nav.html', {'breadcrumbs': true}) }}`
- range (int/array): generate the navigation from a level
    - `{{ navigation('path/to/nav.html', {'range': 2}) }}`

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

### InlineSVG
Adds or replaces attributes to an svg image.

    {{ asset('path/to/image.svg')|inline_svg({attr: {class: 'your-class', id: 'svg-1'}})|raw }}

You can also add some classes:
- `{{ asset('path/to/image.svg')|inline_svg({classes: 'add-classname another-classname'})|raw }}`

### Body class and id
Set the class and the id of the `<body>` tag.

    {% set body_class = 'your-class' %}
    {% set body_id = 'your-id' %}

---

## JavaScripts and Gulp
### Lazyload
Loads pictures, when they come in to the display. You only have to add the `observer.js` JavaScript file in to the
JavaScripts block.

    {% block javascripts %}
        <script type="text/javascript" src="{{ asset( 'assets/js/observer.js' ) }}"></script>
    {% endblock %}

If you added JavaScript to the JavaScript-Block in the `base.html.twig` you have to add `{{ parent() }}` in the
page JavaScript-Block.

    {% block javascripts %}
        <script type="text/javascript" src="{{ asset( 'assets/js/observer.js' ) }}"></script>
        {{ parent() }}
    {% endblock %}

### ServiceWorker
We've added a google [serviceworker](https://developers.google.com/web/tools/workbox/)

### Sass compiling
To write better and cleaner css you can use sass, which you can compile over `gulp styles`.
You only need the `assets/sass/default.sass` where you combine your sass files.

### Watcher
Auto compiles sass and minifys css and js. You can start a watcher with the command `gulp watch`.
If you want to auto refresh the browser after new compiling you can use the browser sync `gulp watch-bs`.

### Favicon
Generates favicons to display on the desktop for every common OS. To add the favicons to your page you have to
include the `favicon.html` in the `base.html.twig`

    {% include 'partial/favicon.html' %}

---

## Useful documentations
- [Symfony](https://symfony.com/doc/current/index.html)
- [Twig](https://twig.symfony.com/doc/2.x/)


## About Us
We are [Field-Interactive](https://www.field-interactive.com)
