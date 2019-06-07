import path from 'path';
import fs from 'fs';

/**
 * Автоматически генерирует список entrypoints постранично.
 *
 * @param {string} mode Режим сборки dev/prod.
 * @return {Object} Объект entry.
 */
export default function generateEntrypoints(mode) {
    // const modeDir = mode === 'dev' ? 'src/static-vue/pages' : 'src/vue/pages';
    let modeDir = 'src/vue/pages';
    let pagesDir = `../../${modeDir}`;

    let pageNames = fs.readdirSync(path.resolve(__dirname, pagesDir));
    const entryObj = {};

    pageNames.forEach(name => {
        entryObj[name] = `./${modeDir}/${name}/${name}.js`;
    });

    return entryObj;
}
