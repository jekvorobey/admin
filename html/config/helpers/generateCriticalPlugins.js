import path from 'path';
import fs from 'fs';
import HtmlCriticalPlugin from 'html-critical-webpack-plugin';

/**
 * Автоматически генерирует список вызовов html-critical-webpack-plugin под все страницы.
 *
 * @see Параметры плагина берутся из документации Critical: {@link https://github.com/addyosmani/critical}
 *
 * @return {Array} Массив из определений плагина.
 */
export default function generateCriticalPlugins() {
    const pagesDir = '../../src/pages';

    const pageNames = fs.readdirSync(path.resolve(__dirname, pagesDir));

    return pageNames.map(
        name =>
            new HtmlCriticalPlugin({
                /* Critical CSS определяется уже после генерации всех файлов, так что указывается папка prod-сборки. */
                base: path.resolve(__dirname, '../../../public/assets'),
                /* Соответственно файл ищется по уже сбилженному пути. */
                src: `html/${name}.html`,
                /* И после внесения правок никуда не перемещается, а остаётся на месте. */
                dest: `html/${name}.html`,
                /* Если не указывать css, то инлайновый код будет дублироваться пропорционально числу файлов (сущностей плагина).
                Поэтому указываем путь напрямую, но это хлипкое решение, т.к. это поле не поддерживает регулярные выражения
                и требует абсолютного совпадения, что не позволяет использовать contenthash в имени
                (нет возможности его получить на этапе сборки). */
                css: `public/styles/${name}.css`,
                /* Critical CSS встраивается инлайном прямо в head. */
                inline: true,
                /* Минифицируется. */
                minify: true,
                /* Шрифты и ссылки игнорируются. */
                ignore: ['@font-face', /url\(/],
            })
    );
}
