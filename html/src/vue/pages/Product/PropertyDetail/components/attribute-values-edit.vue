<template>
    <div class="col-lg-6 col-sm-12">
        <h4 class="mb-4">Атрибут может принимать значения:</h4>
        <div class="mb-4">
            <template v-if="attributeType === 'directory'">
                <template v-if="variants.old.length > 0">
                    <p>Текущие возможные значения:</p>
                    <div v-for="(variant, index) in variants.old" class="input-group mb-3">
                        <input v-model="variants.old[index].name"
                               @input="checkInput"
                               type="text"
                               class="form-control w-25"
                               :class="{'is-invalid': variants.old[index].name.length === 0}"
                               placeholder="Введите значение на замену"
                               :aria-describedby="'control' + variant.id"
                               :disabled="!editState[variant.id]">
                        <input v-if="isColor"
                               v-model="variants.old[index].code"
                               @input="checkInput"
                               type="text"
                               class="form-control"
                               :class="{'is-invalid': isColorInvalid(index, 'old')}"
                               :disabled="!editState[variant.id]"
                               placeholder="#000000">
                        <input v-if="isColor"
                               v-model="variants.old[index].code"
                               type="color"
                               class="form-control"
                               :class="{'is-invalid': isColorInvalid(index, 'old')}"
                               :disabled="!editState[variant.id]">
                        <div class="input-group-append" :id="'control' + variant.id">
                            <button v-if="!editState[variant.id]"
                                    @click="editVariant(variant.id)"
                                    class="btn btn-secondary"
                                    type="button">
                                <fa-icon icon="pencil-alt"/>
                            </button>
                            <button @click="removeVariant(index)" class="btn btn-danger" type="button">
                                <fa-icon icon="trash-alt"/>
                            </button>
                        </div>
                    </div>
                    <hr>
                </template>

                <p v-else>Укажите не менее двух возможных значений для атрибута:</p>

                <b v-if="variants.old.length > 0">Добавляемые значения:</b>
                <div v-for="(variant, index) in variants.new" class="input-group mt-3 mb-3">
                    <input v-model="variants.new[index].name"
                           @input="checkInput"
                           type="text"
                           class="form-control w-25"
                           :placeholder="'Возможное значение ' + (index+1)"
                           :aria-describedby="'deleteButton' + (index+1)">
                    <input v-if="isColor"
                           v-model="variants.new[index].code"
                           @input="checkInput"
                           type="text"
                           class="form-control"
                           :class="{'is-invalid': isColorInvalid(index, 'new')}"
                           placeholder="#000000">
                    <input v-if="isColor"
                           v-model="variants.new[index].code"
                           type="color"
                           class="form-control"
                           :class="{'is-invalid': isColorInvalid(index, 'new')}">
                    <div class="input-group-append">
                        <button v-if="isFieldOptional(index)"
                                @click="removeVariant()"
                                class="btn btn-outline-danger"
                                type="button"
                                :id="'deleteButton' + (index+1)">
                            <fa-icon icon="trash-alt"/>
                        </button>
                    </div>
                </div>
                <button @click="addVariant" class="btn btn-dark float-right">
                    <fa-icon icon="plus"/> Добавить значение
                </button>
            </template>

            <template v-else>
                <p class="text-success">Атрибут может принимать любые значения.</p>
                <em>Чтобы установить жестко заданные варианты, выберите тип "Значение из списка"</em>
            </template>
        </div>
    </div>
</template>

<script>
import VInput from "../../../../components/controls/VInput/VInput.vue";
export default {
    components: {VInput},
    props: [
        'attributeType',
        'availableValues',
        'isColor',
        'valuesErrors'
    ],
    data() {
        return {
            variants: {
                old: this.availableValues.old,
                new: this.availableValues.new
            },
            editState: {},
        }
    },
    methods: {
        addVariant() {
            this.variants.new.push({
                name: '',
                code: '#000000'
            });
            this.checkInput();
        },
        removeVariant(index = null) {
            if (index !== null) {
                this.variants.old.splice(index, 1);
                if (this.choicesLeft < 2) this.addVariant();
            } else {
                this.variants.new.splice(this.variants.new.length - 1, 1);
            }
            this.checkInput();
        },
        editVariant(id) {
            this.editState[id] = true;
            this.$forceUpdate();
        },
        /**
         * @param index
         * @return {boolean}
         */
        isFieldOptional(index) {
            return (index === this.variants.new.length - 1) && this.choicesLeft > 2
        },
        /**
         * HEX-код цвета заполнен некорректно?
         * @param index
         * @param storage - смотреть в старых или новых значениях
         * @return {boolean}
         */
        isColorInvalid(index, storage = 'new') {
            return !(/^#[0-9A-F]{6}$/i.test(this.variants[storage][index].code))
        },
        /**
         * Поля в значении заполнены некорректно?
         * @param index
         * @param storage - смотреть в старых или новых значениях
         * @return {boolean}
         */
        isValueInvalid(index, storage = 'new') {
            return (
                this.variants[storage][index].name.length === 0
                || this.isColorInvalid(index, storage)
            )
        },
        checkInput() {
            let errors = [];
            if (this.attributeType === 'directory') {
                // Если старых значений нет
                if (this.variants.old.length === 0) {
                    for (let i = 0; i < 2; i++) {
                        if (this.isValueInvalid(i, 'new')) {
                            errors.push('• Укажите не менее 2 возможных корректных значений');
                            break;
                        }
                    }
                }
                // Если указано только 1 старое значение
                if (this.variants.old.length === 1) {
                    if (this.isValueInvalid(0, 'new')) {
                        errors.push('• Добавьте хотя бы 1 новое корректное значение');
                    }
                }
                // Проверяются все старые значения
                this.variants.old.forEach((item, index) => {
                    if (this.isValueInvalid(index, 'old')) {
                        errors.push('• Введите корректное значение на замену №' + (index+1));
                    }
                })
                // Проверяются все новые значения
                this.variants.new.forEach((item, index) => {
                    if (this.isValueInvalid(index, 'new')) {
                        errors.push('• Введите корректное или удалите новое значение №' + (index+1));
                    }
                })
            }
            this.$emit('update:valuesErrors', errors);
        },
    },
    computed: {
        /** @return {number} */
        choicesLeft() {
            return this.variants.old.length + this.variants.new.length
        },
    },
    created() {
        if (this.variants.old.length > 0) {
            this.variants.new.splice(0, 2);
            this.variants.old.forEach(item => {
                this.editState[item.id] = false;
            });
        }
    }
}
</script>

<style scoped>

</style>