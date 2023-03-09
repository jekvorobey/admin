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
                :placeholder="placeholderText"
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
    import {fioClean, phoneClean} from '../../../utils/validations';

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
        validation: { type: String, default: null },
        placeholderText: {
            type: [String],
            required: false,
            default: null
        }
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
        },
        input(e) {
            switch (this.validation) {
                case 'fio':
                    e.target.value = fioClean(e.target.value)
                    this.$emit('input', e.target.value)
                    break;
                case 'phone':
                    e.target.value = phoneClean(e.target.value)
                    this.$emit('input', e.target.value)
                    break;
                default:
                    this.$emit('input', e.target.value);
            }
        },
    }
};
</script>
