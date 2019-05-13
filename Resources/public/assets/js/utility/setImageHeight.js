import { images } from '../vars';

const setImageHeight = _ => {
    ;['load', 'resize'].forEach(event => {
        window.addEventListener(event, _ => {
            if (!images.length) return false;
            images.forEach(img => {
                const ratio = img.getAttribute('data-ratio');
                const width = img.offsetWidth;
                img.style.height = `${Math.floor(width / ratio)}px`;
            });
        });
    });
};

export { setImageHeight };
