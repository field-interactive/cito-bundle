import LazyLoad from 'vanilla-lazyload';
import { setImageHeight } from '../utility/setImageHeight';

const loadPolyfill = async _ => {
    if (!('IntersectionObserver' in window)) {
        const module = await import('intersection-observer');
        module.default();
    }
};

const lazyload = _ => {
    loadPolyfill();
    setImageHeight();
    const lazyloadInstanze = new LazyLoad([
        {
            elements_selector: '.lazy'
        },
        {}
    ]);
};

export { lazyload };
