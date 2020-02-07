<template>
    <div class="form-group">
        <label :for="inputId">
            <slot />
        </label>
        <input
                v-if="tag === 'input'"
                v-bind="$attrs"
                v-on="inputListeners"
                class="form-control"
                :class="{ 'is-invalid': error }"
                :id="inputId"
                :value="value"
                :type="type"
                :aria-describedby="`${inputId}-alert`"
        />
        <textarea
                v-if="tag === 'textarea'"
                v-bind="$attrs"
                v-on="inputListeners"
                class="form-control"
                :class="{ 'is-invalid': error }"
                :id="inputId"
                :value="value"
                :type="type"
                :aria-describedby="`${inputId}-alert`"
        />
        <small :id="`${inputId}-help`" class="form-text text-muted" v-if="help">{{ help }}</small>
        <span :id="`${inputId}-alert`" class="invalid-feedback" role="alert">
            <slot name="error" :error="error">
                {{ error }}
            </slot>
        </span>
    </div>
</template>

<script>
import inputMixin from '../../../mixins/input-mixin';

const validTags = ['input', 'textarea'];

export default {
    name: 'v-input',
    inheritAttrs: false,
    mixins: [inputMixin],
    props: {
        value: {},
        help: { type: String, default: '' },
        type: { type: String, default: 'text' },
        tag: {
            type: String,
            default: 'input',
            validator(value) {
                return validTags.indexOf(value) !== -1;
            },
        },
    },
    data() {
        return {
            inputId: `v-input-id-${this._uid}`,
        };
    },
};
</script>
