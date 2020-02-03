/**
 * Скрипт масок полей ввода.
 * Добавляет маски в поля ввода через плагин jquery-mask-plugin.
 *
 * @module Mask
 *
 * @see {@link https://vuejs-tips.github.io/vue-the-mask/}
 *
 */

import Vue from 'vue';
import MaskPlugin from '../vue/plugins/MaskPlugin';

Vue.use(MaskPlugin);

// Tokens
//     '#': {pattern: /\d/},
//     'X': {pattern: /[0-9a-zA-Z]/},
//     'S': {pattern: /[a-zA-Z]/},
//     'A': {pattern: /[a-zA-Z]/, transform: v => v.toLocaleUpperCase()},
//     'a': {pattern: /[a-zA-Z]/, transform: v => v.toLocaleLowerCase()},
//     '!': {escape: true}

// <input type="tel" v-mask="'+7(###) ###-##-##'" />

export const telMask = '+7(###) ###-##-##';
