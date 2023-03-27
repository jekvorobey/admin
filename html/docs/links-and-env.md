#### Работа с ENV
- На фронте работа с env проиходит с помощью dotenv-webpack.
- Подключение и работа происходит в файле webpack.config.babel.js
- Вызов доступен в любом месте, пример - `process.env.SHOWCASE_HOST`

#### Ссылки на сторонние ресурсы
- Реализован mixin - show-case.js (`html/src/vue/mixins/show-case.js`)
- Можно дополнять различными методам для перехода между ресурсами 