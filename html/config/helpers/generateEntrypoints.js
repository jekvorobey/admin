import path from 'path';
import fs from 'fs';

/**
 * Автоматически генерирует список entrypoints постранично.
 *
 * @param {string} mode Режим сборки dev/prod.
 * @return {Object} Объект entry.
 */
export default function generateEntrypoints(mode) {
    let pagesDir = path.resolve(__dirname, `../../src/vue/pages`);
    const entryObj = {};

    addDir(pagesDir, '', entryObj);
    return entryObj;
}

function addDir(basePath, relativePath, entryList) {
    let fullPath = path.resolve(basePath, relativePath);
    let files = fs.readdirSync(fullPath, {withFileTypes: true}),
        jsFiles = files.filter(file => {
            return file.isFile() && file.name.endsWith('.js');
        }),
        isEntry = jsFiles.length > 0;
    if (isEntry) {
        let entryName = relativePath.substring(basePath.length + 1).replace("\\", '/');
        entryList[entryName] = path.resolve(fullPath, jsFiles[0].name);
    }
    let folders = files.filter(file => {
        return file.isDirectory() && file.name !== 'components';
    });

    if (folders.length > 0) {
        folders.forEach(folder => {
            let subfolderPath = path.resolve(fullPath, folder.name);
            addDir(basePath, subfolderPath, entryList);
        });
    }
}
