/** ************************************* svg ************************************************************************* */
/** ************************************* css ************************************************************************* */
import '../styles/customMedia.css';
import '../styles/customProperties.css';
import '../styles/customSelectors.css';
import '../styles/app.css';

import 'focus-visible';
import './mask';
import './validation';
import 'lazysizes';

import Vue from 'vue';
import LayoutMain from '../vue/components/layout/Main.vue';
import breadcrumbs from '../vue/components/breadcrumbs/breadcrumbs.vue';

/** ************************************* /svg ************************************************************************* */

/** ************************************* css external **************************************************************** */
import 'bootstrap/dist/css/bootstrap.css'
import 'bootstrap-vue/dist/bootstrap-vue.css'
/** ************************************* /css external **************************************************************** */

/** ************************************* /css ************************************************************************* */

/** ************************************* js ************************************************************************* */

/** ************************************* /js ************************************************************************* */

Vue.component('layout-main', LayoutMain);
Vue.component('breadcrumbs', breadcrumbs);
