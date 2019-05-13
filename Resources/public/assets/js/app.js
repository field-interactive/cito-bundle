import jQuery from 'jquery';
import { lazyload } from './modules/lazyload';
import { cookies } from './modules/cookies';

jQuery(document).ready(_ => {
    lazyload();
    cookies();
});
