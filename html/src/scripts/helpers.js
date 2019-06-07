/**
 * @module Helpers
 */

/**
 * Класс, содержащий набор статичных вспомогательных функций.
 * </pre>
 */
export default class Helpers {
    /**
     * Создаёт массив из всех целый чисел от start до end.
     *
     * @param  {number} start Первое значение.
     * @param  {number} end   Последнее значение.
     * @return {number[]}        Массив значений.
     */
    static range(start, end) {
        return Array(end - start + 1)
            .fill()
            .map((item, index) => start + index);
    }

    /**
     * Переводит переменную даты в строку.
     *
     * @param  {Date}   date Объект даты.
     * @return {string}      Строка даты.
     */
    static dateToString(date) {
        const day = `0${date.getDate()}`.slice(-2);
        const month = `0${date.getMonth() + 1}`.slice(-2);
        const year = date.getFullYear();

        return `${day}.${month}.${year}`;
    }

    /**
     * Проверка на touch device.
     *
     * @return {boolean} Результат проверки.
     */
    static isTouch() {
        return 'ontouchstart' in window;
    }

    /**
     * Превращает значение любого типа в массив.
     *
     * @param {*} arg Аргумент любого типа.
     */
    static toArray(arg) {
        return [].concat(...[arg]);
    }

    /**
     * Вычисляет контрольное число.
     *
     * @param  {string}   str          Исходная строка.
     * @param  {number[]} coefficients Коэффициенты контрольной суммы.
     * @return {number}                Контрольное число.
     */
    static countCheckdigit(str, coefficients) {
        const checksum = coefficients.reduce((sum, coefficient, index) => sum + coefficient * str[index], 0);

        return (checksum % 11) % 10;
    }

    /**
     * Получить только числа из строки.
     *
     * @param  {string} str Исходная строка.
     * @return {string} Str.
     */
    static rawPhone(str) {
        return str.match(/\d+/g).join('');
    }

    /**
     *  Выбор нужной формы слова.
     *
     *   Forms = [
     *     "банан",
     *     "банана",
     *     "бананов"
     *   ];.
     *
     *   Plural_form(1, forms); //банан
     *   plural_form(2, forms); //банана
     *   plural_form(5, forms); //бананов.
     *
     * @param n
     * @param forms
     * @return String.
     */
    static plural_form(n, forms) {
        return n % 10 == 1 && n % 100 != 11
            ? forms[0]
            : n % 10 >= 2 && n % 10 <= 4 && (n % 100 < 10 || n % 100 >= 20)
            ? forms[1]
            : forms[2];
    }

    /**
     * Make "10000" like "10 000".
     *
     * @param number
     * @param decimals
     * @param dec_point
     * @param thousands_sep
     * @returns {string | void}
     */
    static preparePrice(number, decimals, dec_point, thousands_sep) {
        let i;
        let j;
        let kw;
        let kd;
        let km;
        let minus = '';

        // input sanitation & defaults
        if (isNaN((decimals = Math.abs(decimals)))) {
            decimals = 2;
        }
        if (dec_point === undefined) {
            dec_point = ',';
        }
        if (thousands_sep === undefined) {
            thousands_sep = ' ';
        }

        if (number < 0) {
            minus = '-';
            number *= -1;
        }

        i = `${parseInt((number = (+number || 0).toFixed(decimals)))}`;
        if ((j = i.length) > 3) {
            j %= 3;
        } else {
            j = 0;
        }
        km = j ? i.substr(0, j) + thousands_sep : '';
        kw = i.substr(j).replace(/(\d{3})(?=\d)/g, `$1${thousands_sep}`);
        kd =
            decimals && Math.abs(number - i) > 0
                ? dec_point +
                  Math.abs(number - i)
                      .toFixed(decimals)
                      .replace(/-/, 0)
                      .slice(2)
                : '';

        return minus + km + kw + kd;
    }

    /**
     * Проверка на наличие класса.
     *
     * @param {*} element DOM Элемент.
     * @param {string} className Класс.
     */
    static hasClass(element, className) {
        return new RegExp(`(\\s|^)${className}(\\s|$)`).test(element.className);
    }

    /**
     * Добавить класс.
     *
     * @param {*} element DOM Элемент.
     * @param {string} className Класс.
     */
    static addClass(element, className) {
        if (!Helpers.hasClass(element, className)) element.className += ` ${className}`;
    }

    /**
     * Удалить класс.
     *
     * @param {*} element DOM Элемент.
     * @param {string} className Класс.
     */
    static removeClass(element, className) {
        const classRegex = new RegExp(`(\\s|^)${className}(\\s|$)`);
        element.className = element.className.replace(classRegex, ' ').trim();
    }

    /**
     * Скролит контейнер на определенное значение с анимацией.
     *
     * @param {*} element DOM Элемент.
     * @param {number} to Конечное значение скролла.
     * @param {number} duration Время.
     */
    static scrollTo(element, to, duration = 1000) {
        const start = element.scrollTop;
        const change = to - start;
        let currentTime = 0;
        const increment = duration === 0 ? 0 : 20;

        // t = current time
        // b = start value
        // c = change in value
        // d = duration
        const easeInOutQuad =
            duration === 0
                ? () => to
                : (t, b, c, d) => {
                      t /= d / 2;
                      if (t < 1) return (c / 2) * t * t + b;
                      t -= 1;
                      return (-c / 2) * (t * (t - 2) - 1) + b;
                  };

        let interval;
        const animateScroll = () => {
            currentTime += increment;
            const val = easeInOutQuad(currentTime, start, change, duration);
            element.scrollTop = val;
            if (currentTime >= duration) clearInterval(interval);
        };
        interval = setInterval(animateScroll, increment);
    }

    /* eslint-disable func-names */
    /* eslint-disable prefer-rest-params */
    static debounce(fn, time = 500) {
        let timeout;

        return function() {
            const functionCall = () => fn.apply(this, arguments);

            clearTimeout(timeout);
            timeout = setTimeout(functionCall, time);
        };
    }
    /* eslint-enable func-names */
    /* eslint-enable prefer-rest-params */
}
