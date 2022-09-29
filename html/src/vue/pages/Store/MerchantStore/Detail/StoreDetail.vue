<template>
    <layout-main back>
        <form v-on:submit.prevent.stop="save" class="mt-3">
            <div class="row">
                <v-select
                        v-model="$v.store.merchant_id.$model"
                        :options="merchantOptions"
                        :error="error_merchant_id"
                        class="col-lg-6 col-12"
                >Мерчант</v-select>
            </div>
            <div class="row">
                <v-input
                        v-model="$v.store.name.$model"
                        :error="error_store_name"
                        class="col-lg-4 col-8"
                >Название склада</v-input>
                <v-input
                        v-model="store.xml_id"
                        class="col-lg-2 col-4"
                >Внешний код</v-input>
            </div>
            <div class="row">
                <v-dadata
                        :value="$v.store.address.address_string.$model"
                        :error="error_store_address"
                        @onSelect="onStoreAddressAdd"
                        class="col-lg-6 col-12"
                >Адрес</v-dadata>
            </div>
            <div class="row">
                <v-input
                        v-model="store.address.porch"
                        class="col-lg-2 col-4"
                >Подъезд</v-input>
                <v-input
                        v-model="store.address.floor"
                        class="col-lg-2 col-4"
                >Этаж</v-input>
                <v-input
                        v-model="store.address.intercom"
                        class="col-lg-2 col-4"
                >Домофон</v-input>
            </div>
            <div class="row">
                <v-input
                        type="textarea"
                        v-model="store.address.comment"
                        class="col-lg-6 col-12"
                >Комментарий к адресу</v-input>
            </div>
            <div class="row">
                <v-input
                        v-model="store.dpd_regular_num"
                        class="col-lg-6 col-12"
                >Номер регулярного заказа DPD</v-input>
            </div>
            <div class="col-lg-6 col-12 form-group">
                <div class="row">
                    <label>
                        Всегда сплитом
                        <input type="checkbox" v-model="store.is_always_splitted"/>
                    </label>
                </div>
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
            <tr v-for="(day, index) in store.storeWorking" :class="!day.active ? 'inactive' : ''">
                <td>
                    <input type="checkbox" v-model="day.active" @change="updateWorking(index)"/>
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
                            @change="updateWorking(index)"
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
                            @change="updateWorking(index)"
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
                        <span class="font-weight-normal"><br>Создание заявки</span>
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
                        <template v-if="pickupTimeOptions(deliveryService.id).length > 0">
                            <v-select
                                    v-model="store.pickupTimes[index][deliveryService.id]['pickup_time_code']"
                                    :options="pickupTimeOptions(deliveryService.id)"
                                    @change="savePickupTime(store.pickupTimes[index][deliveryService.id])">
                            </v-select>
                        </template>
                        <template v-else>
                            <date-picker
                                    v-model="store.pickupTimes[index][deliveryService.id]['pickup_time_start']"
                                    input-class="form-control form-control-sm"
                                    type="time"
                                    format="HH:mm"
                                    value-type="format"
                                    :lang="langFrom"
                                    :time-picker-options="timePickerOptions"
                                    @change="preparePickupTime(index, deliveryService.id)"
                            />
                            <date-picker
                                    v-model="store.pickupTimes[index][deliveryService.id]['pickup_time_end']"
                                    input-class="form-control form-control-sm"
                                    type="time"
                                    format="HH:mm"
                                    value-type="format"
                                    :lang="langTo"
                                    :time-picker-options="{
                                        start: store.pickupTimes[index][deliveryService.id]['pickup_time_start'] || '00:30',
                                        step: timePickerOptions.step,
                                        end: timePickerOptions.end
                                    }"
                                    @change="preparePickupTime(index, deliveryService.id)"
                            />
                        </template>
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
                        @change="updateContact(index)"
                    />
                </td>
                <td>
                    <input
                        v-model="contact.phone"
                        class="form-control form-control-sm"
                        type="text"
                        @change="updateContact(index)"
                    />
                </td>
                <td>
                    <input
                        v-model="contact.email"
                        class="form-control form-control-sm"
                        type="text"
                        @change="updateContact(index)"
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
        <div class="col-12 mt-3" v-if="canUpdate(blocks.stores)">
            <button type="submit"
                    class="btn btn-success"
                    @click="updateStore()">
                Сохранить изменения
            </button>
        </div>
    </layout-main>
</template>

<script>
    import Services from '../../../../../scripts/services/services';
    import VDadata from '../../../../components/controls/VDaData/VDaData.vue';
    import VInput from '../../../../components/controls/VInput/VInput.vue';
    import VSelect from '../../../../components/controls/VSelect/VSelect.vue';
    import DatePicker from 'vue2-datepicker';
    import 'vue2-datepicker/index.css';
    import 'vue2-datepicker/locale/ru.js';

    import {validationMixin} from 'vuelidate';
    import {integer, required, requiredIf} from 'vuelidate/lib/validators';

    export default {
    name: 'page-stores-detail',
    components: {
        VInput,
        DatePicker,
        VSelect,
        VDadata,
    },
    props: {
        iStore: [Object, null],
        merchants: [Array],
        pickupTimes: [Object],
    },
    mixins: [validationMixin],
    data() {
        return {
            store: this.iStore,
            changeStore: {
                days: {},
                pickupTimes: {},
                deleteContacts: [],
            },
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
        }
    },
    mounted() {
        for(let prop in this.store.address) {
            if (this.$v.store.address.hasOwnProperty(prop)) {
                this.$v.store.address[prop].$model = this.store.address[prop];
            }
        }
    },
    validations() {
        let self = this;

        return {
            store: {
                merchant_id: {integer, required},
                name: {required},
                address: {
                    address_string: {required},
                    country_code: {required},
                    post_index: {required},
                    region: {required},
                    region_guid: {required},
                    city: {required},
                    city_guid: {required},
                    house: {
                        required: requiredIf(() => {
                            return !self.store.address.block;
                        })
                    },
                    block: {
                        required: requiredIf(() => {
                            return !self.store.address.house;
                        })
                    },
                }
            }
        };
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
        pickupTimeOptions(deliveryService) {
            return this.pickupTimes.hasOwnProperty(deliveryService) ? Object.values(this.pickupTimes[deliveryService])
                .reduce((acc, pickupTime, i) => {
                    if (i === 0) acc.push({value: null, text: ''});
                    acc.push({value: pickupTime.id, text: pickupTime.name});
                    return acc;
                }, []) : [];
        },
        onStoreAddressAdd(suggestion) {
            let address = suggestion.data;

            this.store.address.address_string = suggestion.unrestricted_value;
            this.store.address.country_code = address.country_iso_code;
            this.store.address.post_index = address.postal_code;
            this.store.address.region = address.region_with_type;
            this.store.address.region_guid = address.region_fias_id;
            this.store.address.area = address.area_with_type;
            this.store.address.area_guid = address.area_fias_id;

            let assignSettlementAsCity = address.settlement_with_type && !['автодорога', 'мкр'].includes(address.settlement_type);
            this.store.address.city = assignSettlementAsCity ? address.settlement_with_type : address.city_with_type;
            this.store.address.city_guid = assignSettlementAsCity ? address.settlement_fias_id : address.city_fias_id;

            this.store.address.street = address.street_with_type;
            this.store.address.house = address.house ? [address.house_type, address.house].join(' ') : '';
            this.store.address.block = address.block ? [address.block_type, address.block].join(' ') : '';
            this.store.address.flat = address.flat ? [address.flat_type, address.flat].join(' ') : '';
        },
        updateWorking(index) {
            this.changeStore.days[index] = this.store.storeWorking[index];
        },
        preparePickupTime(index, deliveryServiceId) {
            if (
                this.store.pickupTimes[index][deliveryServiceId]['pickup_time_start']
                >=
                this.store.pickupTimes[index][deliveryServiceId]['pickup_time_end']
            ) {
                this.store.pickupTimes[index][deliveryServiceId]['pickup_time_end'] = null;
                return;
            }

            this.savePickupTime(this.store.pickupTimes[index][deliveryServiceId])
        },
        savePickupTime(pickupTime) {
            let day = pickupTime.day;
            let delivery_service;
            if (pickupTime.delivery_service) {
                delivery_service = pickupTime.delivery_service;
            } else {
                delivery_service = 'all';
            }

            if (!this.changeStore.pickupTimes[day]) {
                this.changeStore.pickupTimes[day] = {};
            }
            this.changeStore.pickupTimes[day][delivery_service] = pickupTime;
        },
        createContact() {
            let contact = {
                name: '',
                email: '',
                phone: '',
                store_id: this.store.id,
            };

            this.store.storeContact.push(contact);
        },
        updateContact(index) {
            if (this.store.storeContact[index].id) {
                this.store.storeContact[index].update = true;
            }
        },
        deleteContact(index) {
            if (this.store.storeContact[index].id) {
                this.changeStore.deleteContacts.push(this.store.storeContact[index].id);
            }

            this.store.storeContact.splice(index, 1);
        },
        initChangeStore() {
            this.changeStore.days = {};
            this.changeStore.pickupTimes = {};
            this.changeStore.deleteContacts = [];
        },
        updateStore() {
            this.$v.$touch();
            if (this.$v.$invalid) {
                return;
            }

            let updatePromise = Services.net().put(
                this.getRoute('merchantStore.update', {id: this.store.id}),
                null,
                this.store
            );

            let workingPromises = [];
            for (let [key, day] of Object.entries(this.changeStore.days)) {
                workingPromises.push(Services.net().put(
                    this.getRoute('merchantStore.updateWorking', {id: day.id}),
                    null,
                    day
                ));
            }

            let pickupTimePromises = [];
            for (let [key, day] of Object.entries(this.changeStore.pickupTimes)) {
                for (let [key, pickupTime] of Object.entries(day)) {
                    pickupTimePromises.push(Services.net().put(
                        this.getRoute('merchantStore.savePickupTime'),
                        null,
                        pickupTime
                    ));
                }
            }

            let createContactsPromises = [];
            let createContactsPromisesIndexes = [];
            let updateContactsPromises = [];
            for (let [index, contact] of Object.entries(this.store.storeContact)) {
                if (!contact.id) {
                    createContactsPromises.push(Services.net().post(
                        this.getRoute('merchantStore.createContact', {id: contact.store_id}),
                        null,
                        contact
                    ));
                    createContactsPromisesIndexes.push(index);
                }
                if (contact.update) {
                    delete contact['update'];
                    updateContactsPromises.push(Services.net().put(
                        this.getRoute('merchantStore.updateContact', {id: contact.id}),
                        null,
                        contact
                    ));
                }
            }

            let deleteContactsPromises = [];
            this.changeStore.deleteContacts.forEach(id => {
                    deleteContactsPromises.push(Services.net().delete(
                    this.getRoute('merchantStore.deleteContact', {id: id}),
                    null,
                    null
                ));
            });

            let workingPromisesLength = workingPromises.length;
            let pickupTimePromisesLength = pickupTimePromises.length;
            let createContactsPromisesStartIndex = workingPromisesLength + pickupTimePromisesLength + 1;
            let createContactsPromisesLastIndex = createContactsPromisesStartIndex + createContactsPromises.length;

            Services.showLoader();
            Promise.all([
                updatePromise,
                ...workingPromises,
                ...pickupTimePromises,
                ...createContactsPromises,
                ...updateContactsPromises,
                ...deleteContactsPromises,
            ]).then(data => {
                for (let i = createContactsPromisesStartIndex; i < createContactsPromisesLastIndex; i++) {
                    if(data[i].id) {
                        this.store.storeContact[createContactsPromisesIndexes[i - createContactsPromisesStartIndex]].id = data[i].id;
                    }
                }
                Services.net().get(
                    this.getRoute('merchantStore.pickupTime'), {store_id: this.store.id}
                ).then(data => {
                    this.store.pickupTimes = data.pickupTimes;
                    Services.msg("Изменения сохранены");
                }).finally(() => {
                    this.initChangeStore();
                    Services.hideLoader();
                });
            });
        },
    },
    computed: {
        merchantOptions() {
            return Object.values(this.merchants).map(merchant => ({value: merchant.id, text: merchant.legal_name}));
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
        error_store_address() {
            if (this.$v.store.address.address_string.$dirty) {
                if (!this.$v.store.address.address_string.required) {
                    return "Введите адрес и выберите его из подсказки ниже";
                }
            }
            if (this.$v.store.address.post_index.$dirty) {
                if (!this.$v.store.address.post_index.required) {
                    return "Введите почтовый индекс";
                }
            }
            if (this.$v.store.address.region.$dirty) {
                if (!this.$v.store.address.region.required) {
                    return "Введите регион";
                }
            }
            if (this.$v.store.address.city.$dirty) {
                if (!this.$v.store.address.city.required) {
                    return "Введите город/населенный пункт";
                }
            }
            if (this.$v.store.address.house.$dirty || this.$v.store.address.block.$dirty) {
                if (!this.$v.store.address.house.required || !this.$v.store.address.block.required) {
                    return "Введите дом/строение/корпус";
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
