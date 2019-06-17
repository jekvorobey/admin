import '../../app';
import Vue from 'vue';
import store from '../../store/store';
import media from '../../plugins/MediaPlugin';
import Registration from './Registration.vue';

import(/* webpackChunkName: 'fonts.preload' */ '../../../scripts/fonts');

// Boot the current Vue component
const root = document.getElementById('app');

window.vue = new Vue({
    store,
    mq: media,
    render: h =>
        h(Registration, {
            props: JSON.parse(root.dataset.props),
        }),
}).$mount(root);
