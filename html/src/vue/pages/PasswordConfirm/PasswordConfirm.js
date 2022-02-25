import '../../app';
import Vue from 'vue';
import store from '../../store/store';
import media from '../../plugins/MediaPlugin';
import PasswordConfirm from './PasswordConfirm.vue';

// Boot the current Vue component
const root = document.getElementById('app');

window.vue = new Vue({
    store,
    mq: media,
    render: h =>
        h(PasswordConfirm, {
            props: JSON.parse(root.dataset.props),
        }),
}).$mount(root);
