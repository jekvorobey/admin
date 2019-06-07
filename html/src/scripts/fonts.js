/**
 * Скрипт подгрузки шрифтов с использованием паттерна Critical FOFT with preload.
 * Использует Font Face Observer в качестве полифилла для Font Loading API.
 * Если шрифт на проекте не поддерживает сабсеттинг из-за ограничений лицензии, использовать обычный FOFT паттерн.
 *
 * @module Fonts
 *
 * @see {@link https://www.zachleat.com/web/comprehensive-webfonts/#critical-foft-preload}
 * @see {@link https://fontfaceobserver.com/}
 */

import FontFaceObserver from 'fontfaceobserver';
import Helpers from './helpers';

// Фукнция
(() => {
    const html = document.querySelector('html');

    /* Если шрифты закэшированы, то реальные файлы подгрузятся мгновенно, и повторять загрузку не нужно. */
    if (sessionStorage.fontsLoaded) {
        Helpers.addClass(html, 'fonts-stage-2');
        return;
    }

    const montserratCritical = new FontFaceObserver('montserrat-critical');

    /* На 1-ой стадии подгружается шрифт стандартного начертания с критическим сабсетом,
    а прочие начертания симулируются браузером. */
    Promise.all([montserratCritical.load(null, 10000)]).then(() => {
        Helpers.addClass(html, 'fonts-stage-1');

        const montserratRegular = new FontFaceObserver('montserrat');
        const montserratBold = new FontFaceObserver('montserrat', {
            weight: 700,
        });
        const montserratLight = new FontFaceObserver('montserrat', {
            weight: 300,
        });
        const montserratMedium = new FontFaceObserver('montserrat', {
            weight: 500,
        });

        /* На 2-ой стадии загружаются реальные файлы шрифтов. */
        Promise.all([
            montserratRegular.load(null, 10000),
            montserratBold.load(null, 10000),
            montserratLight.load(null, 10000),
            montserratMedium.load(null, 10000),
        ]).then(() => {
            Helpers.removeClass(html, 'fonts-stage-1');
            Helpers.addClass(html, 'fonts-stage-2');

            sessionStorage.fontsLoaded = true;
        });
    });
})();
