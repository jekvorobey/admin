<template>
    <div>
        <textarea
                v-bind="$attrs"
                v-on="inputListeners"
                ref="input"
                class="form-control"
                :value="value"
                :type="type"
                :placeholder="this.placeholderName"
        />
    </div>
</template>

<script>
    export default {
        name: 'f-text-area-search',
        inheritAttrs: false,
        props: {
            value: {},
            type: { type: String, default: 'text' },
            placeholderName: {type: String, default: ''}
        },
        methods: {
            input(e) {
                // Парсинг переноса строк и табуляции, формирования массива - разделитель запятая
                this.$emit('input', e.target.value.replace(/[\r\n\t]+/g, ',').split(','))
            },
        },
        computed: {
            inputListeners() {
                return Object.assign({}, this.$listeners, {input: this.input});
            },
        }
    }
</script>
