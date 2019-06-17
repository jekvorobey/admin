import '../../app';
import Vue from 'vue';
import store from '../../store/store';
import media from '../../plugins/MediaPlugin';
import Test from './Test.vue';

// Boot the current Vue component
const root = document.getElementById('app');

window.vue = new Vue({
    store,
    mq: media,
    render: h =>
        h(Test, {
            props: JSON.parse(root.dataset.props),
        }),
}).$mount(root);
