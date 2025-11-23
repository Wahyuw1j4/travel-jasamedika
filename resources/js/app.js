import './bootstrap';
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import PrimeVue from 'primevue/config';
import Aura from '@primeuix/themes/aura';
import { createPinia } from 'pinia';
import 'primeicons/primeicons.css';
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate';
import ToastService from 'primevue/toastservice';

const pinia = createPinia();
pinia.use(piniaPluginPersistedstate);

createInertiaApp({
    resolve: name => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
        return pages[`./Pages/${name}.vue`];
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(pinia)
            .use(plugin)
            .use(ToastService)
            .use(PrimeVue, {
                theme: {
                    preset: Aura,
                    options: {
                        darkModeSelector: '.app-dark',
                        // optional:
                        // prefix: 'p',
                        // cssLayer: false,
                    },
                },
            })
            .mount(el);
    },
});
