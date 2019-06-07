/* eslint-disable require-jsdoc */
import Vue from 'vue';
import VeeValidate, { Validator } from 'vee-validate';
import { validationMixin as mixin } from 'vuelidate';
import {
    helpers,
    required as r,
    minLength as minl,
    maxLength as maxl,
    minValue as minv,
    maxValue as maxv,
    sameAs as sa,
} from 'vuelidate/lib/validators';

import Regex from './regex';
import Helpers from './helpers';

Vue.use(VeeValidate);
// dictionary
const dictionary = {
    ru: {
        messages: {
            required: () => 'Обязательное поле',
            email: () => 'name@domain.сom',
            confirmed: () => `Поле не совпадает`,
            max: (field, [length]) => `Не более ${length} символов`,
            min: (field, [length]) => `Не менее ${length} символов`,
        },
    },
};

Validator.localize('ru', dictionary.ru);

Validator.extend('name', {
    getMessage: () => 'Только латинские и русские буквы, тире и пробелы',
    validate: value => Regex.nameAll.test(value),
});

Validator.extend('nameRu', {
    getMessage: () => 'Только русские буквы, тире и пробелы',
    validate: value => Regex.nameRu.test(value),
});

Validator.extend('nameEn', {
    getMessage: () => 'Только латинские буквы, тире и пробелы',
    validate: value => Regex.nameEn.test(value),
});

Validator.extend('password', {
    getMessage: () => 'Пароль должен содержать как минимум 1 заглавную и строчную латинские буквы и 1 цифру',
    validate: value => Regex.password.test(value),
});

Validator.extend('tel', {
    getMessage: () => 'Формат номера: +7(000) 000-00-00',
    validate: value => Regex.tel.test(value),
});

Validator.extend('kpp', {
    getMessage: () => 'Некорректный формат КПП',
    validate: value => Regex.kpp.test(value),
});

Validator.extend('inn', {
    getMessage: () => 'Некорректный формат ИНН',
    validate: value => {
        const inn = value;

        let isCorrect = false;
        let checkdigit;
        let firstCheckdigit;
        let secondCheckdigit;

        /* ИНН может быть 10 или 12-значным и в каждом случае имеет свою логику проверки */
        switch (inn.length) {
            case 10:
                checkdigit = Helpers.countCheckdigit(inn, [2, 4, 10, 3, 5, 9, 4, 6, 8]);
                if (checkdigit === parseInt(inn[9], 10)) {
                    isCorrect = true;
                }
                break;
            case 12:
                firstCheckdigit = Helpers.countCheckdigit(inn, [7, 2, 4, 10, 3, 5, 9, 4, 6, 8]);
                secondCheckdigit = Helpers.countCheckdigit(inn, [3, 7, 2, 4, 10, 3, 5, 9, 4, 6, 8]);
                if (firstCheckdigit === parseInt(inn[10], 10) && secondCheckdigit === parseInt(inn[11], 10)) {
                    isCorrect = true;
                }
                break;
            default:
        }

        return isCorrect;
    },
});

Validator.extend('minLength', {
    getMessage(field, [length]) {
        return `Выберите не менее ${length} вариантов.`;
    },
    validate(value, [length]) {
        const l = +length;
        return value.length >= l;
    },
});

Validator.extend('maxLength', {
    getMessage(field, [length]) {
        return `Выберите не более ${length} вариантов.`;
    },
    validate(value, [length]) {
        const l = +length;
        return value.length <= l;
    },
});

Validator.extend('maxSymbols', {
    getMessage(field, [length]) {
        return `Введено более ${length} символов.`;
    },
    validate(value, [length]) {
        const l = +length;
        return value.length <= l;
    },
});

export const password = helpers.regex('password', Regex.password);
export const tel = helpers.regex('tel', Regex.tel);
export const email = helpers.regex('email', Regex.email);
export const required = r;
export const sameAs = sa;
export const minLength = minl;
export const maxLength = maxl;
export const minValue = minv;
export const maxValue = maxv;
export const validationMixin = mixin;
