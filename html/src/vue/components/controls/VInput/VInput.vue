<template>
    <div :class="{ 'form-group': group }">
        <label :for="inputId" v-if="this.$slots.default">
            <slot />
        </label>
        <input
                v-if="tag === 'input'"
                v-bind="$attrs"
                v-on="inputListeners"
                ref="input"
                class="form-control"
                :class="{ 'is-invalid': error, 'form-control-sm': sm  }"
                :id="inputId"
                :value="value"
                :type="type"
                :aria-describedby="`${inputId}-alert`"
        />
        <textarea
                v-if="tag === 'textarea'"
                v-bind="$attrs"
                v-on="inputListeners"
                ref="input"
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
        group: { type: Boolean, default: true },
        sm: { type: Boolean, default: false },
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

    methods: {
        copy() {
            this.$refs.input.select();

            if (typeof document !== 'undefined') {
                document.execCommand('copy')
            }

            return this.value;
        }
    }
};
</script>
