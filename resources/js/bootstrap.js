import * as bootstrap from 'bootstrap';
import { createPopper } from '@popperjs/core';

window.bootstrap = bootstrap;
window.Popper = { createPopper };

const nonce = document.head.querySelector('meta[name="nonce"]');

if (nonce) {
    window.__vite_nonce__ = nonce.content;
}
