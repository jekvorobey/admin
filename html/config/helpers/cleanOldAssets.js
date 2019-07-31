import path from 'path';
import fs from 'fs';
const rimraf = require ('rimraf').sync;
/**
 * Очищает все устаревшие ассеты из переданной директории public.
 *
 * @param {string[]} actualAssets Список имён актуальных ассетов.
 * @param {string}   dirName      Имя директории.
 */
function removeOldAssets(actualAssets, dirName) {
    const buildDir = '../../../public/assets/';

    const currentAssets = fs.readdirSync(path.resolve(__dirname, `${buildDir}/${dirName}`), {withFileTypes: true});

    const oldAssets = currentAssets.filter(file => !actualAssets[`${dirName}/${file.name}`]);

    oldAssets.forEach(file => {
        if (file.isDirectory()) {
            rimraf(path.resolve(__dirname, `${buildDir}/${dirName}/${file.name}`));
        } else {
            fs.unlinkSync(path.resolve(__dirname, `${buildDir}/${dirName}/${file.name}`));
        }
    });
}

/**
 * Очищает директорию public от устаревших ассетов.
 */
export default function cleanOldAssets() {
    return {
        apply: compiler => {
            compiler.hooks.afterEmit.tap('AfterEmitPlugin', compilation => {
                const { assets } = compilation;

                const dirNames = ['scripts'];

                dirNames.forEach(name => removeOldAssets(assets, name));
            });
        },
    };
}
