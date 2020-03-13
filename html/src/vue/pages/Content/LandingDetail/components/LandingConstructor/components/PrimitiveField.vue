<template>
    <div class="form-group"
         :class="{ 'required': prop.required, 'validation-error': errors.has(validationName(prop, true)) }">
        <a class="spoiler"
           :class="{ 'spoiler-checked': showProp }"
           @click="toggleCollapse"
           v-if="prop.spoiler"
        >
            {{ prop.spoiler }}
        </a>

        <div class="widget-settings__item" v-if="prop.label" v-show="showProp">
            <label v-if="prop.type !== 'boolean'"
                   class="control-label widget-settings__item-label">
                {{ prop.label }}:
            </label>

            <vue-tooltip
                    v-if="prop.type !== 'boolean' && prop.tooltip"
                    v-show="prop.isInShownList"
                    :text="prop.tooltip"
                    :link="prop.tooltip_href"
            ></vue-tooltip>

            <input v-if="prop.type === 'string'"
                   type="text"
                   data-vv-scope="content"
                   :data-vv-name="validationName(prop)"
                   class="form-control widget-settings__item-text"
                   :maxlength="prop.max_length"
                   v-validate="{
                       required: prop.required,
                       numeric: !!prop.is_numeric || !!prop.is_margin,
                       isPrice: !!prop.is_price
                   }"
                   :required="prop.required"
                   v-model="prop.value">

            <textarea v-else-if="prop.type === 'textarea'"
                      data-vv-scope="content"
                      :data-vv-name="validationName(prop)"
                      class="form-control widget-settings__item-textarea"
                      :rows="prop.textarea_size"
                      v-validate="{ required: prop.required, correctHtml: shouldCheckHtml }"
                      :data-vv-delay="shouldCheckHtml ? 1000 : 0"
                      :required="prop.required"
                      v-model="prop.value"
            ></textarea>

            <div v-if="prop.type === 'boolean'" class="checkbox widget-settings__item-checkbox">
                <input type="checkbox"
                       data-vv-scope="content"
                       :data-vv-name="validationName(prop)"
                       v-validate="{ required: prop.required }"
                       :required="prop.required"
                       v-model="prop.value">
                {{ prop.label }}

                <vue-tooltip
                        v-if="prop.type === 'boolean' && prop.tooltip"
                        v-show="prop.isInShownList"
                        :text="prop.tooltip"
                        :link="prop.tooltip_href"
                ></vue-tooltip>
            </div>

            <select v-if="prop.type === 'select' && prop.selectType === 'select'"
                    data-vv-scope="content"
                    :data-vv-name="validationName(prop)"
                    class="form-control widget-settings__item-select"
                    v-validate="{ required: prop.required }"
                    :required="prop.required"
                    v-model="prop.value"
            >
                <option v-if="!prop.required" value=""></option>
                <option v-for="(optionName, optionValue) in prop.options"
                        :value="optionValue"
                >{{ optionName }}</option>
            </select>

            <div v-if="prop.type === 'select' && prop.selectType === 'radio'"
                 class="widget-settings__item-radio">
                <div v-for="(optionName, optionValue) in prop.options"
                     class="widget-settings__item-radio__option">
                    <input type="radio"
                       data-vv-scope="content"
                       :data-vv-name="validationName(prop)"
                       v-validate="{ required: prop.required }"
                       :required="prop.required"
                       :value="optionValue"
                       v-model="prop.value">
                    {{ optionName }}
                </div>
            </div>

            <div class="validation-error-message" v-show="errors.has(validationName(prop, true))">
                {{ errors.first(validationName(prop, true)) }}
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        inject: ['$validator'],
        props: ['prop'],
        data() {
            return {
                showProp: true,
            };
        },
        methods: {
            toggleCollapse() {
                this.showProp = !this.showProp;
            },
            validationName(prop, full = false) {
                const unique_code = prop.code + this._uid;
                return full ? 'content.' + unique_code : unique_code;
            },
        },
        computed: {
            shouldCheckHtml() {
                return this.prop.code === 'children';
            },
        },
        created() {
            let vm = this;

            this.$validator.extend('isPrice', {
                getMessage: field => 'Цена должна иметь формат 199,99',
                validate: value => /^(\d+),(\d+)$/.test(value),
            });

            this.$validator.extend('correctHtml', {
                getMessage(field, params, data) {
                    return data && data.message;
                },
                validate(value) {
                    let isCorrect = true, errorMessage = '';

                    // todo Какая-нибудь валидация, если понадобится

                    return new Promise(resolve => {
                        resolve({
                            valid: isCorrect,
                            data: { message: "Допущена ошибка в разметке: " + errorMessage }
                        });
                    });
                },
            });
        },
        mounted() {
            this.showProp = !this.prop.hidden && !this.prop.spoiler;
        }
    }
</script>
