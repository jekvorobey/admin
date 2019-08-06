import './app';
import Vue from 'vue';
import store from './store/store';
import media from './plugins/MediaPlugin';

export default function boot(pageComponent, options = {}) {
    // Boot the current Vue component
    const root = document.getElementById('app');
    const vueOptions = Object.assign({
        store,
        mq: media,
        render: h =>
            h(pageComponent, {
                props: JSON.parse(root.dataset.props),
            }),
    }, options);
    window.vue = new Vue(vueOptions).$mount(root);
}