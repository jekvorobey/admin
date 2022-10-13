// Ограничевает ввод (русские, английские буквы и пробел)
export const fioClean = str => str.replace(/[^ A-Za-zЁА-яё]/g, '');

// Не дает вводить +, 7, 8 в начале (работает в связке с v-mask)
export const phoneClean = str => '+7' + str.replace(/^[+,7,8]+/, '')