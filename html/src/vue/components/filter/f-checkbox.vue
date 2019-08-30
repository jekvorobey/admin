<template>
    <div class="form-group">
        <label :for="id">
            <slot />
            <fa-icon v-if="$slots.help" icon="question-circle" v-b-popover.hover="$slots.help[0].text"></fa-icon>
        </label>
        <div class="form-check">
            <input
                    :id="id"
                    v-bind="$attrs"
                    v-on="inputListeners"
                    class="form-check-input"
                    :checked="value"
                    type="checkbox">
            <label class="form-check-label" :for="id"><slot name="yes">Да</slot></label>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'f-checkbox',
        inheritAttrs: false,
        props: {
            value: {},
        },
        computed: {
            id() {
                return `filter-input-${this._uid}`
            },
            inputListeners() {
                return Object.assign({}, this.$listeners, {input: this.input});
            },
        },
        methods: {
            input(e) {
                this.$emit('input', e.target.checked);
            },
        },
    }
</script>