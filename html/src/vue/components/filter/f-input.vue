<template>
    <div class="form-group">
        <label :for="id">
            <slot />
            <fa-icon v-if="$slots.help" icon="question-circle" v-b-popover.hover="$slots.help[0].text"></fa-icon>
        </label>
        <div class="input-group input-group-sm">
            <div v-if="$slots.prepend" class="input-group-prepend"><slot name="prepend"/></div>
            <input
                    :id="id"
                    v-bind="$attrs"
                    v-on="inputListeners"
                    :value="value"
                    :type="type"
                    class="form-control">
            <div v-if="$slots.append" class="input-group-append"><slot name="append"/></div>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'f-input',
        inheritAttrs: false,
        props: {
            value: {},
            type: { type: String, default: 'text' }
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
                this.$emit('input', e.target.value);
            },
        },
    }
</script>