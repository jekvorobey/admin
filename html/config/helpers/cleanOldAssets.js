import path from 'path';
import fs from 'fs';

/**
 * Очищает все устаревшие ассеты из переданной директории public.
 *
 * @param {string[]} actualAssets Список имён актуальных ассетов.
 * @param {string}   dirName      Имя директории.
 */
function removeOldAssets(actualAssets, dirName) {
    const buildDir = '../../../public/assets/';

    const currentAssets = fs.readdirSync(path.resolve(__dirname, `${buildDir}/${dirName}`));

    const oldAssets = currentAssets.filter(file => !actualAssets[`${dirName}/${file}`]);

    oldAssets.forEach(file => fs.unlinkSync(path.resolve(__dirname, `${buildDir}/${dirName}/${file}`)));
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
