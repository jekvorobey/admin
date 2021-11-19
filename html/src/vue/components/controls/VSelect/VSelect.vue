<template>
    <div class="form-group">
        <label :for="inputId" v-if="this.$slots.default">
            <slot />
        </label>

        <b-form-select
            v-bind="$attrs"
            v-on="$listeners"
            :id="inputId"
            class="form-control"
            :class="{ 'is-invalid': error }"
            :aria-describedby="`${inputId}-alert`"
            :options="options"
        ></b-form-select>

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

    export default {
        name: "VSelect",

        mixins: [inputMixin],

        props: {
            help: { type: String, default: '' },

            nullableValue: {
                default: null
            },

            placeholder: {
                type: String,
                default: 'Пожалуйста, укажите элемент из списка'
            }
        },

        data() {
            return {
                inputId: `v-input-id-${this._uid}`,
            };
        },

        computed: {
            options() {
                if (
                    typeof this.$attrs.options === 'undefined') {
                    return [];
                }

                let needAddDefaultOptions = true;
                let options = [];

                if (Array.isArray(this.$attrs.options)) {
                    options = [ ...this.$attrs.options ];
                } else {
                    for (const optionKey in this.$attrs.options) {
                        options.push(this.prepareOptionValue(
                            this.$attrs.options[optionKey],
                            optionKey
                        ));
                    }
                }

                for (const option of options) {
                    if (option.value === this.nullableValue) {
                        needAddDefaultOptions = false;
                    }
                }

                if (!needAddDefaultOptions) {
                    return options;
                }

                return [
                    { value: this.nullableValue, text: this.placeholder, disabled: true },
                    ...options
                ];
            }
        },

        methods: {
            prepareOptionValue(unpreparedOption, unpreparedOptionKey) {
                let preparedOption = {};

                if (
                    typeof unpreparedOption === 'object' &&
                    !Array.isArray(unpreparedOption)
                ) {
                    preparedOption = Object.assign(preparedOption, unpreparedOption);
                } else {
                    preparedOption.text = unpreparedOption;
                    preparedOption.value = unpreparedOptionKey;
                }

                return preparedOption;
            }
        }
    }
</script>
