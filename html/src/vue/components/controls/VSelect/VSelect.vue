<template>
    <div class="form-group">
        <label :for="inputId">
            <slot />
        </label>
        <b-form-select v-bind="$attrs"
                       v-on="$listeners"
                       :id="inputId"
                       class="form-control"
                       :class="{ 'is-invalid': error }"
                       :aria-describedby="`${inputId}-alert`"
        ></b-form-select>
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
        name: "VSelect",
        mixins: [inputMixin],
        data() {
            return {
                inputId: `v-input-id-${this._uid}`,
            };
        },
    }
</script>