import merge from 'webpack-merge';

import analyzePreset from '../presets/webpack.analyze.babel';
import buddyPreset from '../presets/webpack.buddy.babel';

/* В идеале здесь должен быть динамический импорт, но он по непонятной причине не работает.
Другой вариант - это динамический require, но тогда пришлось бы писать все пресеты через module.exports.
Так что, чтобы сохранить единый ESM стиль, пресеты перечисляются здесь вручную.
Все существующие пресеты импортируются, создаётся объект соответствий имён и конфигов, и затем из них выбираются только нужные. */
const presets = {
    analyze: analyzePreset,
    buddy: buddyPreset,
};

export default presetNames => {
    /* Независимо от типа аргумента приводим его к массиву. */
    const mergedPresetNames = [].concat(...[presetNames]);
    const configs = [];

    const presetsArr = Object.keys(presets);

    presetsArr.forEach(presetName => {
        if (mergedPresetNames.includes(presetName)) {
            configs.push(presets[presetName]);
        }
    });

    return merge({}, ...configs);
};
