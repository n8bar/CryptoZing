import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Session-expiry handling for AJAX. When any AJAX call hits a 419 (expired
// session / stale CSRF token), the server returns a JSON `redirect` hint and
// has already stashed the return target via the request Referer — so we send
// the user to the same /login?expired surface as a full-page bounce, and they
// land back here after signing in. Funnelling axios + fetch through one helper
// keeps the recovery story identical regardless of how the request was made.
function redirectToLoginOnExpiry(redirectUrl) {
    window.location.assign(redirectUrl || '/login?expired=1');
}

window.axios.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error?.response?.status === 419) {
            redirectToLoginOnExpiry(error.response.data?.redirect);
        }

        return Promise.reject(error);
    },
);

const nativeFetch = window.fetch.bind(window);
window.fetch = async (...args) => {
    const response = await nativeFetch(...args);

    if (response.status === 419) {
        let redirectUrl;

        try {
            redirectUrl = (await response.clone().json())?.redirect;
        } catch {
            // No JSON body — fall back to the default login URL.
        }

        redirectToLoginOnExpiry(redirectUrl);
    }

    return response;
};

// Belt-and-suspenders for return-to-page on full-page form posts. The 419
// handler's primary signal is the Referer header (sent in full for same-origin
// posts by default), but a stricter referrer policy or privacy setting can
// strip it. Stamping the current URL into a hidden `_return_to` field at submit
// time guarantees the return target rides in the POST body, where it survives
// even when the session and CSRF token are gone. Covers every POST form,
// including ones added later, with no per-form wiring.
document.addEventListener(
    'submit',
    (event) => {
        const form = event.target;

        if (! (form instanceof HTMLFormElement)) {
            return;
        }

        if ((form.getAttribute('method') || 'get').toLowerCase() !== 'post') {
            return;
        }

        if (form.querySelector('input[name="_return_to"]')) {
            return;
        }

        const field = document.createElement('input');
        field.type = 'hidden';
        field.name = '_return_to';
        field.value = window.location.href;
        form.appendChild(field);
    },
    true,
);
