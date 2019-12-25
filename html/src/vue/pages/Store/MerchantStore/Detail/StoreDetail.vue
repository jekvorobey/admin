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
                    :error="error_store_name"
                    class="col-lg-6 col-12"
                    @change="update">Название склада</v-input>
            </div>
            <div class="row">
                <v-input
                    v-model="store.xml_id"
                    class="col-lg-2 col-4">
                    Внешний код
                </v-input>
                <v-input
                    v-model="$v.store.zip.$model"
                    :error="error_store_zip"
                    class="col-lg-2 col-4"
                    @change="update">
                    Индекс
                </v-input>
                <v-input
                    v-model="$v.store.city.$model"
                    :error="error_store_city"
                    class="col-lg-2 col-4"
                    @change="update">Город</v-input>
            </div>
            <div class="row">
                <v-input
                    v-model="$v.store.street.$model"
                    :error="error_store_street"
                    class="col-lg-2 col-4"
                    @change="update">Улица</v-input>
                <v-input
                    v-model="store.house"
                    class="col-lg-2 col-4"
                    @change="update">Дом</v-input>
                <v-input
                    v-model="store.flat"
                    class="col-lg-2 col-4"
                    @change="update">Квартира/Офис</v-input>
            </div>
        </form>

        <hr class="mt-3">
        <h3 class="mb-3">График работы</h3>
        <table class="table table-condensed">
            <thead>
            <tr>
                <th>ID</th>
                <th>День недели</th>
                <th>Время работы</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="(day, index) in store.days" :class="!day.active ? 'inactive' : ''">
                <td>
                    <input type="checkbox" v-model="day.active" @change="updateWorking(day)"/>
                </td>
                <td>
                    {{ dayName(index) }}
                </td>
                <td>
                    <div class="form-group">
                        <date-picker
                            v-model="day.working_start_time"
                            input-class="form-control form-control-sm"
                            type="time"
                            format="HH:mm"
                            value-type="format"
                            :lang="langFrom"
                            :time-picker-options="timePickerOptions"
                            :disabled="!day.active"
                            @change="updateWorking(day)"
                        />
                        <date-picker
                            v-model="day.working_end_time"
                            input-class="form-control form-control-sm"
                            type="time"
                            format="HH:mm"
                            value-type="format"
                            :lang="langTo"
                            :time-picker-options="timePickerOptions"
                            :disabled="!day.active"
                            @change="updateWorking(day)"
                        />
                    </div>
                </td>
            </tr>
            </tbody>
        </table>

        <hr class="mt-3">
        <h3 class="mb-3">График отгрузки</h3>
        <div class="scroll-x">
            <table class="table table-condensed">
                <thead>
                <tr>
                    <th>День недели</th>
                    <th>
                        Все службы доставки
                        <span class="font-weight-normal"><br>Создание заявки<br>Отгрузка товара</span>
                    </th>
                    <th v-for="deliveryService in deliveryServices">
                        {{deliveryService.name}}
                        <span class="font-weight-normal"><br>Создание заявки<br>Отгрузка товара</span>
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="(day, index) in store.pickupTimes">
                    <td>{{ dayName(index) }}</td>
                    <td>
                        <date-picker
                            v-model="store.pickupTimes[index]['all']['cargo_export_time']"
                            input-class="form-control form-control-sm"
                            type="time"
                            format="HH:mm"
                            value-type="format"
                            :lang="langAt"
                            :time-picker-options="timePickerOptions"
                            @change="savePickupTime(store.pickupTimes[index]['all'])"
                        /><br>
                        <date-picker
                            v-model="store.pickupTimes[index]['all']['pickup_time']"
                            input-class="form-control form-control-sm"
                            type="time"
                            format="HH:mm"
                            value-type="format"
                            :lang="langAt"
                            :time-picker-options="timePickerOptions"
                            @change="savePickupTime(store.pickupTimes[index]['all'])"
                        />
                    </td>
                    <td v-for="deliveryService in deliveryServices">
                        <date-picker
                            v-model="store.pickupTimes[index][deliveryService.id]['cargo_export_time']"
                            input-class="form-control form-control-sm"
                            type="time"
                            format="HH:mm"
                            value-type="format"
                            :lang="langAt"
                            :time-picker-options="timePickerOptions"
                            @change="savePickupTime(store.pickupTimes[index][deliveryService.id])"
                        /><br>
                        <date-picker
                            v-model="store.pickupTimes[index][deliveryService.id]['pickup_time']"
                            input-class="form-control form-control-sm"
                            type="time"
                            format="HH:mm"
                            value-type="format"
                            :lang="langAt"
                            :time-picker-options="timePickerOptions"
                            @change="savePickupTime(store.pickupTimes[index][deliveryService.id])"
                        />
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <hr class="mt-3">
        <h3 class="mb-3">Контактные лица</h3>
        <table class="table table-condensed">
            <thead>
            <tr>
                <th>Контактное лицо</th>
                <th>Телефон</th>
                <th>Email</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="(contact, index) in store.storeContact">
                <td>
                    <input
                        v-model="contact.name"
                        class="form-control form-control-sm"
                        type="text"
                        @change="updateContact(contact)"
                    />
                </td>
                <td>
                    <input
                        v-model="contact.phone"
                        class="form-control form-control-sm"
                        type="text"
                        @change="updateContact(contact)"
                    />
                </td>
                <td>
                    <input
                        v-model="contact.email"
                        class="form-control form-control-sm"
                        type="text"
                        @change="updateContact(contact)"
                    />
                </td>
                <td>
                    <fa-icon icon="trash-alt" @click="deleteContact(index)"></fa-icon>
                </td>
            </tr>
            <tr>
                <td colspan="3" class="text-center">
                    <button @click="createContact" class="btn btn-sm btn-dark">Добавить контакт <fa-icon icon="plus"></fa-icon></button>
                </td>
                <td></td>
            </tr>
            </tbody>
        </table>
    </layout-main>
</template>

<script>
import Service from '../../../../../scripts/services/services';
import {mapGetters} from "vuex";
import VInput from '../../../../components/controls/VInput/VInput.vue';
import VSelect from '../../../../components/controls/VSelect/VSelect.vue';
import DatePicker from 'vue2-datepicker';

import {validationMixin} from 'vuelidate';
import {integer, required} from 'vuelidate/lib/validators';

export default {
    name: 'page-stores-detail',
    components: {
        VInput,
        DatePicker,
        VSelect
    },
    props: {
        iStore: [Object, null],
        iDeliveryServices: [Object, null],
        merchants: [Array]
    },
    mixins: [validationMixin],
    data() {
        return {
            store: this.iStore,
            langFrom: {
                placeholder: {
                    date: 'от',
                }
            },
            langTo: {
                placeholder: {
                    date: 'до',
                }
            },
            langAt: {
                placeholder: {
                    date: 'в',
                }
            },
            timePickerOptions: {
                start: '00:00',
                step: '00:30',
                end: '23:30'
            },
            deliveryServices: this.iDeliveryServices
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
        dayName(id) {
            id = parseInt(id);
            switch (id) {
                case 1:
                    return 'Понедельник';
                case 2:
                    return 'Вторник';
                case 3:
                    return 'Среда';
                case 4:
                    return 'Четверг';
                case 5:
                    return 'Пятница';
                case 6:
                    return 'Суббота';
                case 7:
                    return 'Воскресенье';
            }
        },
        update() {
            this.$v.$touch();
            if (this.$v.$invalid) {
                return;
            }

            Service.net().put(
                this.getRoute('merchantStore.update', {id: this.store.id}),
                null,
                this.store
            ).then(data => {
            });
        },
        updateWorking(day) {
            Service.net().put(
                this.getRoute('merchantStore.updateWorking', {id: day.id}),
                null,
                day
            ).then(data => {
            });
        },
        savePickupTime(pickupTime) {
            Service.net().put(
                this.getRoute('merchantStore.savePickupTime'),
                null,
                pickupTime
            ).then(data => {
                this.store.pickupTimes = data.pickupTimes;
            });
        },
        createContact() {
            let contact = {
                name: '',
                email: '',
                phone: '',
                store_id: this.store.id
            };

            Service.net().post(
                this.getRoute('merchantStore.createContact', {id: contact.store_id}),
                null,
                contact
            ).then(data => {
                if(data.id) {
                    contact.id = data.id;
                    this.store.storeContact.push(contact);
                }
            });
        },
        updateContact(contact) {
            Service.net().put(
                this.getRoute('merchantStore.updateContact', {id: contact.id}),
                null,
                contact
            ).then(data => {
            });
        },
        deleteContact(index) {
            let id = this.store.storeContact[index].id;
            if(id) {
                Service.net().delete(
                    this.getRoute('merchantStore.deleteContact', {id: id}),
                    null,
                    null
                ).then(data => {
                });
            }

            this.store.storeContact.splice(index, 1);
        },

    },
    computed: {
        ...mapGetters(['getRoute']),
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
        error_store_name() {
            if (this.$v.store.name.$dirty) {
                if (!this.$v.store.name.required) {
                    return "Обязательное поле!";
                }
            }
        },
        error_store_zip() {
            if (this.$v.store.zip.$dirty) {
                if (!this.$v.store.zip.required) {
                    return "Обязательное поле!";
                }
            }
        },
        error_store_city() {
            if (this.$v.store.city.$dirty) {
                if (!this.$v.store.city.required) {
                    return "Обязательное поле!";
                }
            }
        },
        error_store_street() {
            if (this.$v.store.street.$dirty) {
                if (!this.$v.store.street.required) {
                    return "Обязательное поле!";
                }
            }
        },
    }
};
</script>
<style>
    .mx-calendar-icon {
        height: auto;
    }
    tr.inactive {
        opacity: 0.5;
    }
    .table .form-group {
        margin-bottom: 0;
    }
    .table .form-group label {
        display: none;
    }
    .scroll-x {
        width: 1024px;
        overflow-x: auto;
    }
    .font-weight-normal {
        font-weight: normal;
    }
</style>
