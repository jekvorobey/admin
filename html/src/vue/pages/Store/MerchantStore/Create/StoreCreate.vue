<template>
    <layout-main back>
        <form v-on:submit.prevent.stop="save" class="mt-3">
            <div class="row">
                <v-select
                    v-model="$v.store.merchant_id.$model"
                    :options="merchantOptions"
                    :error="error_merchant_id"
                    class="col-lg-6 col-12">
                    Мерчант
                </v-select>
            </div>
            <div class="row">
                <v-input
                    v-model="$v.store.name.$model"
                    :error="error_name"
                    class="col-lg-6 col-12">Название склада</v-input>
            </div>
            <div class="row">
                <v-input
                    v-model="store.xml_id"
                    :error="error_zip"
                    class="col-lg-2 col-4">
                    Внешний код
                </v-input>
                <v-input
                    v-model="$v.store.zip.$model"
                    :error="error_zip"
                    class="col-lg-2 col-4">
                    Индекс
                </v-input>
                <v-input
                    v-model="$v.store.city.$model"
                    :error="error_city"
                    class="col-lg-2 col-4">Город</v-input>
            </div>
            <div class="row">
                <v-input
                    v-model="$v.store.street.$model"
                    :error="error_street"
                    class="col-lg-2 col-4">Улица</v-input>
                <v-input
                    v-model="store.house"
                    class="col-lg-2 col-4">Дом</v-input>
                <v-input
                    v-model="store.flat"
                    class="col-lg-2 col-4">Квартира/Офис</v-input>
            </div>
            <button @click="add" type="button" class="btn btn-success mb-3" :disabled="!$v.store.$anyDirty">Добавить</button>
        </form>
    </layout-main>
</template>

<script>
import Service from '../../../../../scripts/services/services';
import VInput from '../../../../components/controls/VInput/VInput.vue';
import VSelect from '../../../../components/controls/VSelect/VSelect.vue';

import { validationMixin } from 'vuelidate';
import { integer, required } from 'vuelidate/lib/validators';

export default {
    name: 'page-stores-detail',
    components: {
        VInput,
        VSelect,
    },
    mixins: [validationMixin],
    props: {
        merchants: [Array]
    },
    data() {
        return {
            store: {
                merchant_id: null,
                name: null,
                xml_id: null,
                zip: null,
                city: null,
                street: null,
                house: null,
                flat: null,
            },
        }
    },
    validations: {
        store: {
            merchant_id: {integer, required},
            name: {required},
            zip: {required},
            city: {required},
            street: {required},
        }
    },
    methods: {
        add() {
            this.$v.$touch();
            if (this.$v.$invalid) {
                return;
            }

            Service.net().post(
                this.getRoute('merchantStore.create'),
                null,
                this.store
            ).then(data => {
                if (data.status === 'ok') {
                    window.location.href = this.route('merchantStore.list');
                }
            });
        },
    },
    computed: {
        merchantOptions() {
            return Object.values(this.merchants).map(merchant => ({value: merchant.id, text: merchant.display_name}));
        },

        error_merchant_id() {
            if (this.$v.store.merchant_id.$dirty) {
                if (!this.$v.store.merchant_id.required) {
                    return "Обязательное поле!";
                }
                if (!this.$v.store.merchant_id.integer) {
                    return "Только целое число!";
                }
            }
        },
        error_name() {
            if (this.$v.store.name.$dirty) {
                if (!this.$v.store.name.required) {
                    return "Обязательное поле!";
                }
            }
        },
        error_zip() {
            if (this.$v.store.zip.$dirty) {
                if (!this.$v.store.zip.required) {
                    return "Обязательное поле!";
                }
            }
        },
        error_city() {
            if (this.$v.store.city.$dirty) {
                if (!this.$v.store.city.required) {
                    return "Обязательное поле!";
                }
            }
        },
        error_street() {
            if (this.$v.store.street.$dirty) {
                if (!this.$v.store.street.required) {
                    return "Обязательное поле!";
                }
            }
        },
    }
};
</script>
