<template>
    <div class="form-group">
        <label :for="inputId" v-if="this.$slots.default">
            <slot />
        </label>
        <input
                v-bind="$attrs"
                v-on="inputListeners"
                :type="type"
                :class="{ 'is-invalid': error }"
                class="form-control"
                :id="inputId"
                :value="value"
                :aria-describedby="`${inputId}-alert`"/>
        <span :id="`${inputId}-alert`" class="invalid-feedback" role="alert">
            <slot name="error" :error="error">
                {{ error }}
            </slot>
        </span>
    </div>
</template>

<script>
    import inputMixin from '../../../mixins/input-mixin';

    export default {
    name: "v-date",
    inheritAttrs: false,
    mixins: [inputMixin],
    props: {
        value: {},
        type: {
            type: String,
            default: 'date',
        },
    },
    data() {
        return {
            inputId: `v-input-id-${this._uid}`,
        };
    },
}
</script>