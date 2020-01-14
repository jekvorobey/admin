<template>
    <div class="form-group">
        <label :for="inputId">
            <slot />
        </label>
        <input
                v-bind="$attrs"
                v-on="inputListeners"
                type="date"
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
    },
    data() {
        return {
            inputId: `v-input-id-${this._uid}`,
        };
    },
}
</script>