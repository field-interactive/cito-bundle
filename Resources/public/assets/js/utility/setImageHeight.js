import jQuery from 'jquery';

const setImageHeight = el => {
    const $el = jQuery(el);
    const ratio = $el.data('ratio');
    const width = $el.width();
    $el.css('height', `${Math.floor(width / ratio)}px`);
};

export { setImageHeight };
