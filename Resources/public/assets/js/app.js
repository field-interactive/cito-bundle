import jQuery from 'jquery';
import { cookies } from './modules/cookies';
import { setImageHeight } from './utility/setImageHeight';

((w, d) => {
    const b = d.getElementsByTagName('body')[0];
    const s = d.createElement('script');
    s.async = true;
    s.defer = true;
    const v = !('IntersectionObserver' in w) ? '8.7.1' : '12.0.0';
    s.src = 'https://cdnjs.cloudflare.com/ajax/libs/vanilla-lazyload/' + v + '/lazyload.min.js';
    w.lazyLoadOptions = [{
        callback_loaded: el => {
            setImageHeight(el);
        }
    }];
    b.appendChild(s);
})(window, document);

jQuery(document).ready(_ => {
    cookies();
});
