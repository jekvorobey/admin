<template>
    <div class="form-group">
        <label :for="id">
            <slot />
            <fa-icon v-if="$slots.help" icon="question-circle" v-b-popover.hover="$slots.help[0].text"></fa-icon>
        </label>
        <div class="input-group input-group-sm">
            <div v-if="$slots.prepend" class="input-group-prepend"><slot name="prepend"/></div>
            <select
                    :id="id"
                    v-bind="$attrs"
                    v-on="inputListeners"
                    :value="value"
                    class="form-control">
                <option v-if="!without_none" :value="null">Не выбрано</option>
                <option v-for="option in options" :value="option.value">{{ option.text }}</option>
            </select>
            <div v-if="$slots.append" class="input-group-append"><slot name="append"/></div>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'f-select',
        inheritAttrs: false,
        props: {
            value: {},
            options: Array,
            without_none: Boolean,
        },
        computed: {
            id() {
                return `filter-select-${this._uid}`
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
