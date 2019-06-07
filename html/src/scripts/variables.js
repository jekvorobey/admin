/**
 * Брейкпоинты проекта.
 * @typedef {Object} Breakpoints
 *
 * @property {Number} mobile    Брейкпоинт на 480.
 * @property {Number} mobileLg  Брейкпоинт на 640.
 * @property {Number} tablet    Брейкпоинт на 768.
 * @property {Number} tabletLg  Брейкпоинт на 1024.
 * @property {Number} desktop   Брейкпоинт на 1280.
 * @property {Number} desktopLg Брейкпоинт на 1600.
 * @property {Number} desktopXl Брейкпоинт на 1920.
 */

/**
 * Медиазапросы проекта.
 * @typedef {Object} MediaQueries
 *
 * @property {MediaQueryList} mobile    Медиа-запрос на 480.
 * @property {MediaQueryList} mobileLg  Медиа-запрос на 640.
 * @property {MediaQueryList} tablet    Медиа-запрос на 768.
 * @property {MediaQueryList} tabletLg  Медиа-запрос на 1024.
 * @property {MediaQueryList} desktop   Медиа-запрос на 1280.
 * @property {MediaQueryList} desktopLg Медиа-запрос на 1600.
 * @property {MediaQueryList} desktopXl Медиа-запрос на 1920.
 */

/**
 * Класс глобальных переменных.
 */
export default class Variables {
    /**
     * Брейкпоинты проекта.
     * @type {Breakpoints}
     */
    static breakpoints = {
        mobile: 480,
        mobileLg: 640,
        tablet: 768,
        tabletLg: 1024,
        desktop: 1280,
        desktopLg: 1600,
        desktopXl: 1920,
    };

    /**
     * Медиазапросы проекта.
     * @type {MediaQueries}
     *
     * @example
     * Variables.media('tablet').addListener(...)
     */

    static media = breakpoint =>
        typeof window !== 'undefined'
            ? window.matchMedia(`(max-width: ${Variables.breakpoints[breakpoint] - 1}px)`)
            : null;

    /**
     * Медиазапросы проекта.
     * @type {MediaQueries}
     *
     * @example
     * if (Variables.matchMedia('tablet')) {
     *     // Удовлетворяет медиазапросу
     * } else {
     *     // Не удовлетворяет
     * }
     */

    static matchMedia = breakpoint => {
        const media = Variables.media(breakpoint);
        return media && media.matches;
    };

    /**
     * Текущая локаль, определяемая атрибутом lang.
     *
     * @type {string}
     */
    static lang = () => {
        return typeof document !== 'undefined' ? document.lang : 'ru';
    };
}
