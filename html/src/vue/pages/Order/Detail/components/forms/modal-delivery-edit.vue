<template>
    <b-modal id="modal-delivery-edit" hide-footer ref="modal" size="lg" @hidden="resetModal">
        <template v-slot:modal-title>
            Редактирование доставки {{delivery.number}}
        </template>
        <template v-slot:default="{close}">
            <b-form-row>
                <div class="col-sm-4">
                    <v-select v-model="$v.form.status.$model" :options="deliveryStatusOptions">
                        Статус доставки*
                    </v-select>
                </div>
                <div class="col-sm-4">
                    <v-select v-model="$v.form.tariff_id.$model" :options="tariffOptions">
                        Тариф ЛО*
                    </v-select>
                </div>
                <div class="col-sm-4">
                    <v-date v-model="$v.form.pdd.$model" :error="errorPdd">
                        PDD*
                    </v-date>
                </div>
            </b-form-row>
            <b-form-row v-if="delivery.point_id">
                <b-col>
                    <v-select v-model="$v.form.point_id.$model" :options="pointOptions" @change="onChangePoint">
                        Точка выдачи заказа*
                    </v-select>
                    <template v-if="points[selectedPointId]">
                        <p class="font-weight-bold">{{points[selectedPointId].type.name}} {{points[selectedPointId].name}}</p>
                        <p><span class="font-weight-bold">Адрес:</span> {{points[selectedPointId].address.address_string}}</p>
                        <p><span class="font-weight-bold">Телефон:</span> {{points[selectedPointId].phone}}</p>
                        <p><span class="font-weight-bold">График работы:</span> {{points[selectedPointId].timetable}}</p>
                        <p><span class="font-weight-bold">Способы оплаты:</span> {{points[selectedPointId].has_payment_card ? 'Наличные и банковские карты' : 'Только наличные'}}</p>
                    </template>
                    <template v-else>
                        <p class="font-weight-bold">{{delivery.point.type.name}} {{delivery.point.name}}</p>
                        <p><span class="font-weight-bold">Адрес:</span> {{delivery.point.address.address_string}}</p>
                        <p><span class="font-weight-bold">Телефон:</span> {{delivery.point.phone}}</p>
                        <p><span class="font-weight-bold">График работы:</span> {{delivery.point.timetable}}</p>
                        <p><span class="font-weight-bold">Способы оплаты:</span> {{delivery.point.has_payment_card ? 'Наличные и банковские карты' : 'Только наличные'}}</p>
                    </template>
                </b-col>
            </b-form-row>
            <template v-else>
                <b-form-row>
                    <b-col>
                        <v-dadata v-model.sync="$v.form.delivery_address.address_string.$model"
                                  :error="errorDeliveryAddress"
                                  @onSelect="onDeliveryAddressSelect">
                            Адрес до квартиры/офиса включительно*
                        </v-dadata>
                    </b-col>
                </b-form-row>
                <b-form-row>
                    <div class="col-sm-4">
                        <v-input v-model="$v.form.delivery_address.porch.$model">
                            Подъезд
                        </v-input>
                    </div>
                    <div class="col-sm-4">
                        <v-input v-model="$v.form.delivery_address.floor.$model">
                            Этаж
                        </v-input>
                    </div>
                    <div class="col-sm-4">
                        <v-input v-model="$v.form.delivery_address.intercom.$model">
                            Домофон
                        </v-input>
                    </div>
                </b-form-row>
                <b-form-row>
                    <b-col>
                        <v-input v-model="$v.form.delivery_address.comment.$model" tag="textarea">
                            Комментарий курьеру от клиента
                        </v-input>
                    </b-col>
                </b-form-row>
            </template>
            <b-form-row>
                <div class="col-sm-4">
                    <v-input v-model="$v.form.receiver_name.$model" :error="errorReceiverName">
                        ФИО получателя*
                    </v-input>
                </div>
                <div class="col-sm-4">
                    <v-input v-model="$v.form.receiver_phone.$model"
                             :error="errorReceiverPhone"
                             :placeholder="telPlaceholder"
                             autocomplete="off"
                             v-mask="telMask">
                        Телефон получателя*
                    </v-input>
                </div>
                <div class="col-sm-4">
                    <v-input v-model="$v.form.receiver_email.$model"
                             :error="errorReceiverEmail"
                             :placeholder="emailPlaceholder"
                             autocomplete="off">
                        E-mail получателя
                    </v-input>
                </div>
            </b-form-row>

            <div class="float-right mt-3">
                <b-button @click="close()" variant="outline-primary">Отмена</b-button>
                <button class="btn btn-info" @click="save">Сохранить</button>
            </div>
        </template>
    </b-modal>
</template>

<script>
    import Services from '../../../../../../scripts/services/services.js';
    import VSelect from '../../../../../components/controls/VSelect/VSelect.vue';
    import VInput from '../../../../../components/controls/VInput/VInput.vue';
    import VDate from '../../../../../components/controls/VDate/VDate.vue';
    import VDadata from "../../../../../components/controls/VDaData/VDaData.vue";

    import {email, required, requiredIf} from 'vuelidate/lib/validators';
    import {validationMixin} from 'vuelidate';

    import {telMask} from '../../../../../../scripts/mask';
    import {emailPlaceholder, telPlaceholder} from '../../../../../../scripts/placeholder';

    export default {
        name: 'modal-delivery-edit',
        components: {
            VDate,
            VDadata,
            VInput,
            VSelect,
        },
        props: [
            'modelDelivery',
            'modelOrder',
        ],
        mixins: [
            validationMixin,
        ],
        data() {
            let isCourierDelivery = !this.modelDelivery.point_id;

            return {
                points: {},
                tariffs: {},
                selectedPointId: this.modelDelivery.point_id,

                form: {
                    status: this.modelDelivery.status.id,
                    tariff_id: this.modelDelivery.tariff_id,
                    pdd: this.modelDelivery.pdd_original,
                    receiver_name: this.modelDelivery.receiver_name,
                    receiver_phone: this.modelDelivery.receiver_phone,
                    receiver_email: this.modelDelivery.receiver_email,
                    delivery_address: {
                        address_string: isCourierDelivery ? this.modelDelivery.delivery_address.address_string : '',
                        country_code: isCourierDelivery ? this.modelDelivery.delivery_address.country_code : '',
                        post_index: isCourierDelivery ? this.modelDelivery.delivery_address.post_index : '',
                        region: isCourierDelivery ? this.modelDelivery.delivery_address.region : '',
                        region_guid: isCourierDelivery ? this.modelDelivery.delivery_address.region_guid : '',
                        city: isCourierDelivery ? this.modelDelivery.delivery_address.city : '',
                        city_guid: isCourierDelivery ? this.modelDelivery.delivery_address.city_guid : '',
                        street: isCourierDelivery ? this.modelDelivery.delivery_address.street : '',
                        house: isCourierDelivery ? this.modelDelivery.delivery_address.house : '',
                        block: isCourierDelivery ? this.modelDelivery.delivery_address.block : '',
                        flat: isCourierDelivery ? this.modelDelivery.delivery_address.flat : '',
                        porch: isCourierDelivery ? this.modelDelivery.delivery_address.porch : '',
                        floor: isCourierDelivery ? this.modelDelivery.delivery_address.floor : '',
                        intercom: isCourierDelivery ? this.modelDelivery.delivery_address.intercom : '',
                        comment: isCourierDelivery ? this.modelDelivery.delivery_address.comment : '',
                    },
                    point_id: !isCourierDelivery ? this.modelDelivery.point_id : 0,
                },
            }
        },
        validations() {
            let self = this;
            const notRequired = {required: requiredIf(() => {return false;})};

            let form = {
                status: {required},
                tariff_id: {required},
                pdd: {required},
                receiver_name: {required},
                receiver_phone: {required},
                receiver_email: {email},
            };
            if (this.delivery.point_id) {
                form['point_id'] = {required};
            } else {
                form['delivery_address'] = {
                    address_string: {required},
                    country_code: {required},
                    post_index: {required},
                    region: {required},
                    region_guid: {required},
                    city: {required},
                    city_guid: {required},
                    street: notRequired,
                    house: {
                        required: requiredIf(() => {
                            return !self.form.delivery_address.block;
                        })
                    },
                    block: {
                        required: requiredIf(() => {
                            return !self.form.delivery_address.house;
                        })
                    },
                    flat: notRequired,
                    porch: notRequired,
                    floor: notRequired,
                    intercom: notRequired,
                    comment: notRequired,
                };
            }

            return {
                form: form
            };
        },
        methods: {
            onChangePoint(value) {
                this.selectedPointId = value;
            },
            onDeliveryAddressSelect(suggestion) {
                let address = suggestion.data;

                this.form.delivery_address.address_string = suggestion.unrestricted_value;
                this.form.delivery_address.country_code = address.country_iso_code;
                this.form.delivery_address.post_index = address.postal_code;
                this.form.delivery_address.region = address.region_with_type;
                this.form.delivery_address.region_guid = address.region_fias_id;
                this.form.delivery_address.area = address.area_with_type;
                this.form.delivery_address.area_guid = address.area_fias_id;
                this.form.delivery_address.city = address.settlement_with_type ? address.settlement_with_type :
                    address.city_with_type;
                this.form.delivery_address.city_guid = address.settlement_with_type ? address.settlement_fias_id :
                    address.city_fias_id;
                this.form.delivery_address.street = address.street_with_type;
                this.form.delivery_address.house = address.house ? [address.house_type, address.house].join(' ') : '';
                this.form.delivery_address.block = address.block ? [address.block_type, address.block].join(' ') : '';
                this.form.delivery_address.flat = address.flat ? [address.flat_type, address.flat].join(' ') : '';
                this.$v.$touch();
            },
            save() {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }

                Services.showLoader();
                Services.net().put(this.getRoute('orders.detail.deliveries.save', {id: this.order.id, deliveryId: this.delivery.id}), {}, this.form).then((data) => {
                    this.order = data.order;

                    Services.msg("Изменения сохранены");
                }).finally(() => {
                    Services.hideLoader();
                });
            },
            cancel() {
                this.form.status = this.modelDelivery.status;
                this.form.tariff_id = this.modelDelivery.tariff_id;
                this.form.pdd = this.modelDelivery.pdd;
                this.form.receiver_name = this.modelDelivery.receiver_name;
                this.form.receiver_phone = this.modelDelivery.receiver_phone;
                this.form.receiver_email = this.modelDelivery.receiver_email;
                if (!this.modelDelivery.point_id) {
                    this.form.delivery_address.address_string = this.modelDelivery.delivery_address.address_string;
                    this.form.delivery_address.porch = this.modelDelivery.delivery_address.porch;
                    this.form.delivery_address.floor = this.modelDelivery.delivery_address.floor;
                    this.form.delivery_address.intercom = this.modelDelivery.delivery_address.intercom;
                    this.form.delivery_address.comment = this.modelDelivery.delivery_address.comment;
                } else {
                    this.form.point_id = this.modelDelivery.point_id;
                }
                this.$v.$reset();
            },
            resetModal() {
                this.delivery = {};
            },
        },
        computed: {
            order: {
                get() {return this.modelOrder},
                set(value) {this.$emit('update:modelOrder', value)},
            },
            delivery: {
                get() {return this.modelDelivery},
                set(value) {this.$emit('update:modelDelivery', value)},
            },
            deliveryStatusOptions() {
                return Object.values(this.deliveryStatuses).map(deliveryStatus => ({
                    value: deliveryStatus.id,
                    text: deliveryStatus.name
                }));
            },
            pointOptions() {
                return Object.values(this.points).map(point => ({
                    value: point.id,
                    text: point.address.address_string
                }));
            },
            tariffOptions() {
                return Object.values(this.tariffs).map(tariff => ({
                    value: tariff.id,
                    text: tariff.name + '(ID=' + tariff.xml_id + ')'
                }));
            },
            telMask() {
                return telMask;
            },
            telPlaceholder() {
                return telPlaceholder;
            },
            emailPlaceholder() {
                return emailPlaceholder;
            },
            errorPdd() {
                if (this.$v.form.pdd.$dirty) {
                    if (!this.$v.form.pdd.required) {
                        return "Обязательное поле";
                    }
                }
            },
            errorReceiverName() {
                if (this.$v.form.receiver_name.$dirty) {
                    if (!this.$v.form.receiver_name.required) {
                        return "Обязательное поле";
                    }
                }
            },
            errorReceiverPhone() {
                if (this.$v.form.receiver_phone.$dirty) {
                    if (!this.$v.form.receiver_phone.required) {
                        return "Обязательное поле";
                    }
                }
            },
            errorReceiverEmail() {
                if (this.$v.form.receiver_email.$dirty) {
                    if (!this.$v.form.receiver_email.email) {
                        return "Введите валидный e-mail";
                    }
                }
            },
            errorDeliveryAddress() {
                if (this.$v.form.delivery_address.address_string.$dirty) {
                    if (!this.$v.form.delivery_address.address_string.required) {
                        return "Введите адрес и выберите его из подсказки ниже";
                    }
                }
                if (this.$v.form.delivery_address.post_index.$dirty) {
                    if (!this.$v.form.delivery_address.post_index.required) {
                        return "Введите почтовый индекс";
                    }
                }
                if (this.$v.form.delivery_address.region.$dirty) {
                    if (!this.$v.form.delivery_address.region.required) {
                        return "Введите регион";
                    }
                }
                if (this.$v.form.delivery_address.city.$dirty) {
                    if (!this.$v.form.delivery_address.city.required) {
                        return "Введите город/населенный пункт";
                    }
                }
                if (this.$v.form.delivery_address.house.$dirty || this.$v.form.delivery_address.block.$dirty) {
                    if (!this.$v.form.delivery_address.house.required || !this.$v.form.delivery_address.block.required) {
                        return "Введите дом/строение/корпус";
                    }
                }
            },
        },
        created() {
            Services.showLoader();
            Services.net().get(this.getRoute('orders.detail.deliveries', {id: this.order.id, deliveryId: this.delivery.id})).then(data => {
                this.points = data.points;
                this.tariffs = data.tariffs;
                this.$bvModal.show('modal-delivery-edit');
            }).finally(() => {
                Services.hideLoader();
            });
        }
};
</script>