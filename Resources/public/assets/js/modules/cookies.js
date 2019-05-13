import Cookies from 'js-cookie';
import { notice, agree } from '../vars';

const cookies = _ => {
    if (notice === null) return;

    if (Cookies.get('cookieNotice')) {
        notice.parentNode.removeChild(notice);
    } else {
        notice.classList.add('show');
    }

    agree.addEventListener('click', e => {
        event.preventDefault();
        notice.parentNode.removeChild(notice);
        Cookies.set('cookieNotice', 'accepted', { expires: 365 });
    });
};

export { cookies };
