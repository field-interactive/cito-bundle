import { images } from '../vars';
const setImageHeight = _ => {
    ;['load', 'resize'].forEach(event => {
        window.addEventListener(event, _ => {
            if (!images.length) return false;
            images.forEach(img => {
                const ratio = img.getAttribute('data-ratio');
                setTimeout(_  => {
                    const width = img.offsetHeight;
                    img.style.height = `${Math.floor(width / ratio)}px`;
                }, 30)

            });
        });
    });
};

export { setImageHeight };
