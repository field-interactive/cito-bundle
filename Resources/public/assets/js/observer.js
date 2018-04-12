'use strict';

// get Array of images / picture tags

var images = document.querySelectorAll('picture');

// config for Observer
var config = {
    rootMargin: '50px 0px',
    threshold: 0.01
};

var imgCount = images.length;
var observer = void 0;

// Check if IntersectionObserver is supportet
if (!('IntersectionObserver' in window)) {
    for (var i = 0; i < images.length; i++) {
        var img = images[i];
        var items = toArray(img.children);
        load(items);
    }
} else {
    observer = new IntersectionObserver(onIntersection, config);
    for (var i = 0; i < images.length; i++) {
        var image = images[i];
        observer.observe(image)
    }
}

// Load Images when thex come in viewport
function onIntersection(image) {
    for (var _i = 0; _i < image.length; _i++) {
        var img = image[_i];
        if (img.intersectionRatio > 0) {
            var _items = toArray(img.target.children);
            load(_items);
        }
    }
}

// Load images
function load(items) {
    for (var _i2 = 0; _i2 < items.length; _i2++) {
        var item = items[_i2];
        var src = item.getAttribute('data-src');
        // Set src
        if (item.nodeName == 'IMG') {
            item.setAttribute('src', src);
        } else {
            item.setAttribute('srcset', src);
        }
    }
}

function toArray(obj) {
    var array = [];
    // iterate backwards ensuring that length is an UInt32
    for (var i = obj.length >>> 0; i--;) {
        array[i] = obj[i];
    }
    return array;
}
