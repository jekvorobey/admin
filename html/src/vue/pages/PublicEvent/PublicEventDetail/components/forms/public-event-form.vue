<template>
    <div>
        <v-select v-model="$v.form.type_id.$model" :options="typeOptions">
            Тип события
        </v-select>
        <v-input v-model="$v.form.name.$model" :error="errorName">
            Название события
        </v-input>
        <v-input v-model.lazy="$v.form.code.$model" :error="errorCode">
            Код события
        </v-input>
        <button @click="save"  class="btn btn-dark">Сохранить</button>
    </div>
</template>

<script>
import {
    NAMESPACE,
    ACT_IS_UNIQUE,
    ACT_SAVE_PUBLIC_EVENT
} from '../../../../../store/modules/public-events';
import { validationMixin } from 'vuelidate';
import { required } from 'vuelidate/lib/validators';

import VInput from '../../../../../components/controls/VInput/VInput.vue';
import VSelect from '../../../../../components/controls/VSelect/VSelect.vue';
import {mapActions} from "vuex";

export default {
    mixins: [validationMixin],
    components: {
        VInput,
        VSelect
    },
    props: {
        publicEvent: {}
    },
    data () {
        return {
            form: {
                name: this.publicEvent.name,
                code: this.publicEvent.code,
                type_id: this.publicEvent.type_id,
            },
        };
    },
    validations: {
        form: {
            name: {required},
            code: {
                required,
                isUnique(code) {
                    // todo add debounce!
                    return this.isUnique({code, id: this.publicEvent.id});
                },
                isCode(code) {
                    return /^[0-9a-zA-Z_]+$/.test(code);
                }
            },
            type_id: {required},
        }
    },
    methods: {
        ...mapActions(NAMESPACE, {
            isUnique: ACT_IS_UNIQUE,
            savePublicEvent: ACT_SAVE_PUBLIC_EVENT
        }),
        waitForValidation () {
            return new Promise((resolve) => {
                const unwatch = this.$watch(() => !this.$v.$pending, (isNotPending) => {
                    if (isNotPending) {
                        unwatch();
                        resolve(!this.$v.$invalid)
                    }
                }, {immediate: true})
            })
        },
        async save() {
            await this.$v.$touch();
            const isValid = await this.waitForValidation();
            if (!isValid) {
                return;
            }

            this.savePublicEvent({
                id: this.publicEvent.id,
                data: this.form,
            }).then(() => {
                this.$emit('onSave');
            });
        }
    },
    computed: {
        typeOptions() {
            return this.publicEventTypes.map(type => ({text: type.name, value: type.id}));
        },
        errorName() {
            if (this.$v.form.name.$dirty) {
                if (!this.$v.form.name.required) return "Обязательное поле!";
            }
        },
        errorCode() {
            if (this.$v.form.code.$dirty) {
                if (!this.$v.form.code.required) return "Обязательное поле!";
                if (!this.$v.form.code.$pending &&!this.$v.form.code.isUnique) return "Код должен быть уникальным!";
                if (!this.$v.form.code.isCode) return "Только латиница, цифры и подчёркивание!";
            }
        },
    }
}
</script>

<style scoped>

</style>