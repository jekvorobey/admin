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
                let cleanVal = e.target.value.replace(/(\r\n|\n|\r|\t)/gm, ","),
                    splitVal = cleanVal.split(','),
                    result = splitVal.filter(item => item !== ""),
                    totalResult = [];

                result.forEach(item => totalResult.push(...item.split(' ')))
                this.$emit('input', totalResult);
            },
        },
        computed: {
            inputListeners() {
                return Object.assign({}, this.$listeners, {input: this.input});
            },
        }
    }
</script>
