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
        name: 'f-text-area',
        inheritAttrs: false,
        props: {
            value: {},
            type: { type: String, default: 'text' },
            placeholderName: {type: String, default: ''}
        },
        methods: {
            input(e) {
                this.$emit('input', e.target.value.split(','));
            },
        },
        computed: {
            inputListeners() {
                return Object.assign({}, this.$listeners, {input: this.input});
            },
        }
    }
</script>
