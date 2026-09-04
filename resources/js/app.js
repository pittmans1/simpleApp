import { createApp, createVNode, render, mergeProps } from 'vue';
import { createPinia } from 'pinia';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import './bootstrap';
import componentRegistry from './components.js';
import '../css/app.css';

window.Pusher = Pusher;

if (import.meta.env.VITE_REVERB_APP_KEY) {
	window.Echo = new Echo({
		broadcaster: 'reverb',
		key: import.meta.env.VITE_REVERB_APP_KEY,
		wsHost: import.meta.env.VITE_REVERB_HOST ?? window.location.hostname,
		wsPort: Number(import.meta.env.VITE_REVERB_PORT ?? 8080),
		wssPort: Number(import.meta.env.VITE_REVERB_PORT ?? 8080),
		forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https',
		enabledTransports: ['ws', 'wss'],
	});
}
// const dashboard = document.querySelector('#app');

const app = createApp({ render: () => {} });
app.use(createPinia());
const components = componentRegistry.registerComponents(app);

for (const name in components) {
    const component = components[name];
    const elements = document.querySelectorAll(name);
    elements.forEach((el) => {
        const props = {};
        for (const attr of el.attributes) {
            props[attr.name] = attr.value;
        }
        const vnode = createVNode(component, mergeProps(props));
        render(vnode, el);
    });
}
