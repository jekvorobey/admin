export default {
    props: {
        error: String,
    },
    computed: {
        inputListeners() {
            // `Object.assign` объединяет объекты вместе, чтобы получить новый объект
            return Object.assign(
                {},
                // Мы добавляем все слушатели из родителя
                this.$listeners,
                // Затем мы можем добавить собственные слушатели или
                // перезаписать поведение некоторых существующих.
                {
                    // Это обеспечит, что будет работать v-model на компоненте
                    input: this.input,
                }
            );
        },
    },
    methods: {
        input(e) {
            this.$emit('input', e.target.value);
        },
    },
};
