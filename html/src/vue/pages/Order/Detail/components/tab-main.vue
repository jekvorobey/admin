<template>
    <div>
        <b-card>
            <b-row>
                <b-col>
                    <p class="font-weight-bold">Деньги</p>
                </b-col>
            </b-row>
            <b-row>
                <div class="col-sm-4">
                    <span class="font-weight-bold">Стоимость заказа:</span> {{preparePrice(order.price)}} руб.
                </div>
                <div class="col-sm-4">
                    <span class="font-weight-bold">Стоимость заказа без скидки:</span> {{preparePrice(order.cost)}} руб.
                </div>
                <div class="col-sm-4">
                    <span class="font-weight-bold">Скидка заказа:</span> {{preparePrice(order.discount)}} руб.
                </div>
            </b-row>
            <b-row>
                <div class="col-sm-4">
                    <span class="font-weight-bold">Стоимость товаров:</span> {{preparePrice(order.product_price)}} руб.
                </div>
                <div class="col-sm-4">
                    <span class="font-weight-bold">Стоимость товаров без скидки:</span> {{preparePrice(order.product_cost)}} руб.
                </div>
                <div class="col-sm-4">
                    <span class="font-weight-bold">Скидка на товары:</span> {{preparePrice(order.product_discount)}} руб.
                </div>
            </b-row>
            <b-row>
                <div class="col-sm-4">
                    <span class="font-weight-bold">Стоимость доставки:</span> {{preparePrice(order.delivery_price)}} руб.
                </div>
                <div class="col-sm-4">
                    <span class="font-weight-bold">Стоимость доставки без скидки:</span> {{preparePrice(order.delivery_cost)}} руб.
                </div>
                <div class="col-sm-4">
                    <span class="font-weight-bold">Скидка на доставку:</span> {{preparePrice(order.delivery_discount)}} руб.
                </div>
            </b-row>
            <b-row>
                <div class="col-sm-4">
                    <span class="font-weight-bold">Потрачено бонусов:</span> {{preparePrice(order.spent_bonus)}}
                </div>
                <div class="col-sm-4">
                    <span class="font-weight-bold">Начислено бонусов:</span> {{preparePrice(order.added_bonus)}} руб.
                </div>
            </b-row>
        </b-card>

        <b-card class="mt-4">
            <b-row>
                <b-col>
                    <div class="float-right">
                        <button class="btn btn-success btn-sm" @click="save" :disabled="!$v.form.$anyDirty">
                            Сохранить
                        </button>
                        <button @click="cancel" class="btn btn-outline-danger btn-sm mr-1" :disabled="!$v.form.$anyDirty">
                            Отмена
                        </button>
                    </div>
                </b-col>
            </b-row>
            <b-row>
                <b-col>
                    <p class="font-weight-bold">Получатель</p>
                </b-col>
            </b-row>
            <b-row>
                <div class="col-sm-4">
                    <v-input v-model="$v.form.receiver_name.$model" :error="errorReceiverName">
                        ФИО
                    </v-input>
                </div>
                <div class="col-sm-4">
                    <v-input v-model="$v.form.receiver_phone.$model"
                             :error="errorReceiverPhone"
                             :placeholder="telPlaceholder"
                             autocomplete="off"
                             v-mask="telMask">
                        Телефон
                    </v-input>
                </div>
                <div class="col-sm-4">
                    <v-input v-model="$v.form.receiver_email.$model"
                             :error="errorReceiverEmail"
                             :placeholder="emailPlaceholder"
                             autocomplete="off">
                        E-mail
                    </v-input>
                </div>
            </b-row>
            <template v-if="this.order.courierDelivery">
                <b-row>
                    <b-col>
                        <v-dadata v-model="$v.form.delivery_address.address_string.$model"
                                  :error="errorDeliveryAddress"
                                  @onSelect="onDeliveryAddressSelect">
                            Адрес до квартиры/офиса включительно
                        </v-dadata>
                    </b-col>
                </b-row>
                <b-row>
                    <div class="col-sm-4">
                        <v-input v-model="form.delivery_address.porch">
                            Подъезд
                        </v-input>
                    </div>
                    <div class="col-sm-4">
                        <v-input v-model="form.delivery_address.floor">
                            Этаж
                        </v-input>
                    </div>
                    <div class="col-sm-4">
                        <v-input v-model="form.delivery_address.intercom">
                            Домофон
                        </v-input>
                    </div>
                </b-row>
                <b-row>
                    <b-col>
                        <v-input v-model="form.delivery_address.comment" tag="textarea">
                            Комментарий курьеру от клиента
                        </v-input>
                    </b-col>
                </b-row>
            </template>
            <b-row v-if="this.order.pickupDelivery">
                <b-col>
                    <v-select v-model="$v.form.point_id.$model" :options="pointOptions" @change="onChangePoint">
                        Точка выдачи заказа
                    </v-select>
                    <template v-if="points[selectedPointId]">
                        <p>{{points[selectedPointId].type.name}} {{points[selectedPointId].name}}</p>
                        <p><span class="font-weight-bold">Адрес:</span> {{points[selectedPointId].address.address_string}}</p>
                        <p><span class="font-weight-bold">Телефон:</span> {{points[selectedPointId].phone}}</p>
                        <p><span class="font-weight-bold">График работы:</span> {{points[selectedPointId].timetable}}</p>
                        <p><span class="font-weight-bold">Способы оплаты:</span> {{points[selectedPointId].has_payment_card ? 'Наличные и банковские карты' : 'Только наличные'}}</p>
                    </template>
                </b-col>
            </b-row>
            <b-row>
                <b-col>
                    <v-input v-model="form.manager_comment" tag="textarea">
                        Комментарий менеджера
                    </v-input>
                </b-col>
            </b-row>
        </b-card>
    </div>
</template>

<script>
    import Services from '../../../../../scripts/services/services.js';
    import VSelect from '../../../../components/controls/VSelect/VSelect.vue';
    import VInput from '../../../../components/controls/VInput/VInput.vue';
    import VDadata from "../../../../components/controls/VDaData/VDaData.vue";

    import {email, required, requiredIf} from 'vuelidate/lib/validators';
    import {validationMixin} from 'vuelidate';

    import {telMask} from '../../../../../scripts/mask';
    import {emailPlaceholder, telPlaceholder} from '../../../../../scripts/placeholder';

    export default {
        components: {
            VDadata,
            VInput,
            VSelect,
        },
        props: [
            'model',
        ],
        mixins: [
            validationMixin,
        ],
        data() {
            return {
                points: {},
                selectedPointId: this.model.pickupDelivery ? this.model.pickupDelivery.point_id : 0,

                form: {
                    receiver_name: this.model.firstDelivery.receiver_name,
                    receiver_phone: this.model.firstDelivery.receiver_phone,
                    receiver_email: this.model.firstDelivery.receiver_email,
                    delivery_address: {
                        address_string: this.model.courierDelivery ? this.model.courierDelivery.delivery_address.address_string : '',
                        porch: this.model.courierDelivery ? this.model.courierDelivery.delivery_address.porch : '',
                        floor: this.model.courierDelivery ? this.model.courierDelivery.delivery_address.floor : '',
                        intercom: this.model.courierDelivery ? this.model.courierDelivery.delivery_address.intercom : '',
                        comment: this.model.courierDelivery ? this.model.courierDelivery.delivery_address.comment : '',
                    },
                    point_id: this.model.pickupDelivery ? this.model.pickupDelivery.point_id : 0,
                    manager_comment: this.model.manager_comment,
                },
            }
        },
        validations() {
            let self = this;
            let isCourierDeliveryExist = self.model.courierDelivery && Object.keys(self.model.courierDelivery).length > 0;
            const requiredIfCourierDeliveryExist = {required: requiredIf(() => {
                return isCourierDeliveryExist;
            })};
            const requiredIfPickupDeliveryExist = {required: requiredIf(() => {
                return self.model.pickupDelivery && Object.keys(self.model.pickupDelivery).length > 0;
            })};

            return {
                form: {
                    receiver_name: {required},
                    receiver_phone: {required},
                    receiver_email: {email},
                    delivery_address: {
                        address_string: requiredIfCourierDeliveryExist,
                        country_code: requiredIfCourierDeliveryExist,
                        post_index: requiredIfCourierDeliveryExist,
                        region: requiredIfCourierDeliveryExist,
                        region_guid: requiredIfCourierDeliveryExist,
                        city: requiredIfCourierDeliveryExist,
                        city_guid: requiredIfCourierDeliveryExist,
                        house: {
                            required: requiredIf(() => {
                                return isCourierDeliveryExist && !self.form.delivery_address.block;
                            })
                        },
                        block: {
                            required: requiredIf(() => {
                                return isCourierDeliveryExist && !self.form.delivery_address.house;
                            })
                        },
                    },
                    point_id: requiredIfPickupDeliveryExist,
                }
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
            },
            save() {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }

                Services.showLoader();
                Services.net().put(this.getRoute('order.detail.main.save', {id: this.order.id}), {}, this.form).then(() => {
                    this.order.firstDelivery.receiver_name = this.form.receiver_name;
                    this.order.firstDelivery.receiver_phone = this.form.receiver_phone;
                    this.order.firstDelivery.receiver_email = this.form.receiver_email;
                    if (this.order.courierDelivery) {
                        this.order.courierDelivery.delivery_address.address_string = this.form.delivery_address.address_string;
                        this.order.courierDelivery.delivery_address.country_code = this.form.delivery_address.country_code;
                        this.order.courierDelivery.delivery_address.post_index = this.form.delivery_address.post_index;
                        this.order.courierDelivery.delivery_address.region = this.form.delivery_address.region;
                        this.order.courierDelivery.delivery_address.region_guid = this.form.delivery_address.region_guid;
                        this.order.courierDelivery.delivery_address.area = this.form.delivery_address.area;
                        this.order.courierDelivery.delivery_address.area_guid = this.form.delivery_address.area_guid;
                        this.order.courierDelivery.delivery_address.city = this.form.delivery_address.city;
                        this.order.courierDelivery.delivery_address.city_guid = this.form.delivery_address.city_guid;
                        this.order.courierDelivery.delivery_address.street = this.form.delivery_address.street;
                        this.order.courierDelivery.delivery_address.house = this.form.delivery_address.house;
                        this.order.courierDelivery.delivery_address.block = this.form.delivery_address.block;
                        this.order.courierDelivery.delivery_address.flat = this.form.delivery_address.flat;
                        this.order.courierDelivery.delivery_address.porch = this.form.delivery_address.porch;
                        this.order.courierDelivery.delivery_address.floor = this.form.delivery_address.floor;
                        this.order.courierDelivery.delivery_address.intercom = this.form.delivery_address.intercom;
                        this.order.courierDelivery.delivery_address.comment = this.form.delivery_address.comment;
                    }
                    if (this.order.pickupDelivery) {
                        this.order.pickupDelivery.point_id = this.form.point_id;
                    }
                    this.order.manager_comment = this.form.manager_comment;

                    Services.msg("Изменения сохранены");
                }).finally(() => {
                    Services.hideLoader();
                });
            },
            cancel() {
                this.form.receiver_name = this.order.firstDelivery.receiver_name;
                this.form.receiver_phone = this.order.firstDelivery.receiver_phone;
                this.form.receiver_email = this.order.firstDelivery.receiver_email;
                this.form.delivery_address.address_string = this.order.courierDelivery.delivery_address.address_string;
                this.form.delivery_address.porch = this.order.courierDelivery.delivery_address.porch;
                this.form.delivery_address.floor = this.order.courierDelivery.delivery_address.floor;
                this.form.delivery_address.intercom = this.order.courierDelivery.delivery_address.intercom;
                this.form.delivery_address.comment = this.order.courierDelivery.delivery_address.comment;
                this.form.point_id = this.order.pickupDelivery.point_id;
                this.form.manager_comment = this.order.manager_comment;
            },
        },
        computed: {
            order: {
                get() {return this.model},
                set(value) {this.$emit('update:model', value)},
            },
            pointOptions() {
                return Object.values(this.points).map(point => ({
                    value: point.id,
                    text: point.address.address_string
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
            errorReceiverName() {
                if (this.$v.form.receiver_name.$dirty) {
                    if (!this.$v.form.receiver_name.required) {
                        return "Обязательное поле!";
                    }
                }
            },
            errorReceiverPhone() {
                if (this.$v.form.receiver_phone.$dirty) {
                    if (!this.$v.form.receiver_phone.required) {
                        return "Обязательное поле!";
                    }
                }
            },
            errorReceiverEmail() {
                if (this.$v.form.receiver_email.$dirty) {
                    if (!this.$v.form.receiver_email.email) {
                        return "Введите валидный e-mail!";
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
            if (this.model.pickupDelivery) {
                Services.showLoader();
                Services.net().get(this.getRoute('orders.detail.main', {id: this.model.id})).then(data => {
                    this.points = data.points;
                }).finally(() => {
                    Services.hideLoader();
                });
            }
        }
    }
</script>

<style scoped>

</style>
