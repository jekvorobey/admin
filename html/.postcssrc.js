module.exports = {
    plugins: {
        /* Изменяет url в CSS, чтобы всё корректно собиралось. */
        'postcss-url': {},
        /* Нормализация базовых стилей. Особенность этого плагина в зависимости от Browserslist. */
        'postcss-normalize': {},
        /* Позволяет использовать вложенные селекторы, как в SCSS. */
        'postcss-nested': {},
        /* Аналог babel-preset-env, только для PostCSS: позволяет использовать возможности нового стандарта: https://preset-env.cssdb.org/ */
        'postcss-preset-env': {
            /* Стейджи отключены, так что нужные возможности указываются напрямую, чтобы не тянуть лишний функционал, о котором никто не знает. */
            stage: false,
            features: {
                /* Современный синтаксис для медиа-запросов. */
                'custom-media-queries': {
                    importFrom: 'src/styles/customMedia.css',
                },
                /* CSS-переменные. */
                'custom-properties': {
                    importFrom: 'src/styles/customProperties.css',
                    preserve: false,
                },
                /* Возможность определять кастомные селекторы. */
                'custom-selectors': {
                    importFrom: 'src/styles/customSelectors.css',
                },
                /* Возможность использовать диапазоны в медиазапросах. */
                'media-query-ranges': true,
                /* Позволяет использовать множественные селекторы внутри псевдокласса :not() */
                'not-pseudo-class': true,
                /* Позволяет использовать overflow-wrap вместо word-wrap, что считается более корректным. */
                'overflow-wrap-property': true,
            },
            /* В postcss-preset-env встроен autoprefixer, но его актуальность не гарантируется. Мы хотим использовать последнюю версию, которую подключаем отдельно, так что эту отключаем, чтобы она не перекрывала нашу. */
            autoprefixer: false,
        },
        /* Добавляет сокращение для clearfix в формате clear: fix; */
        'postcss-clearfix': {},
        /* Предоставляет сокращённый синтаксис для генерации треугольников через правило triangle. */
        'postcss-triangle': {},
        /* Автоматически конвертирует px в rem, чтобы была возможность указывать величины в пикселях согласно макету и не париться :) */
        'postcss-pxtorem': {},
        /* Позволяет загружать в background svg с возможностью менять его параметры (например, fill) в формате: background-image: svg-load(<путь>, fill: <hex-цвет>); */
        'postcss-inline-svg': {},
        /* Используем autoprefixer с поддержкой grid. */
        autoprefixer: {
            grid: 'autoplace',
        },
    },
};
