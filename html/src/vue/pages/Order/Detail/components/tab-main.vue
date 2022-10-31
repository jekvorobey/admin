<template>
    <div>
        <b-card>
            <b-row>
                <b-col>
                    <p class="font-weight-bold">Общая информация</p>
                </b-col>
            </b-row>
            <b-row>
                <div class="col-sm-4">
                    <span class="font-weight-bold">Кол-во доставок:</span> {{order.deliveries.length}} шт.
                </div>
                <div class="col-sm-4">
                    <span class="font-weight-bold">Кол-во отправлений:</span> {{order.shipments.length}} шт.
                </div>
            </b-row>
            <b-row>
                <div class="col-sm-4">
                    <span class="font-weight-bold">Кол-во товаров:</span> {{order.total_qty}} шт.
                </div>
                <div class="col-sm-4">
                    <span class="font-weight-bold">Вес заказа:</span> {{order.weight}} г.
                </div>
            </b-row>
            <b-row>
                <div class="col-sm-4">
                    <span class="font-weight-bold">Мерчанты:</span>
                    <template v-for="(merchant, key) in order.merchants">
                        <span v-if="key > 0">, </span><a :href="getRoute('merchant.detail', {id: merchant.id})" target="_blank">{{ merchant.legal_name }}</a>
                    </template>
                </div>
                <div class="col-sm-4">
                    <span class="font-weight-bold">Тип подтверждения :</span> {{order.confirmation_type.name}}
                </div>
            </b-row>
            <b-row class="mt-2">
                <div class="col-sm-4">
                    <span class="font-weight-bold">Стоимость заказа:</span> {{preparePrice(order.price)}} руб.
                </div>
                <div class="col-sm-4">
                    <span class="font-weight-bold">Стоимость заказа без скидки:</span> {{preparePrice(order.cost)}} руб.
                </div>
                <div class="col-sm-4">
                    <span class="font-weight-bold">Примененные скидки:</span>
                    <div v-for="field in order.discounts">{{field.name}}</div>
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
            <b-row>
                <div class="col-sm-4">
                    <span class="font-weight-bold">Потрачено ПС:</span> {{preparePrice(order.spent_certificate)}} руб.
                </div>
            </b-row>
            <b-row class="mt-3">
                <b-col>
                    <b-table-simple hover small caption-top responsive>
                        <b-thead>
                            <b-tr>
                                <b-th>Реферальный партнер</b-th>
                                <b-th>Элемент корзины</b-th>
                                <b-th>Промокод</b-th>
                            </b-tr>
                        </b-thead>
                        <b-tbody>
                            <b-tr v-for="referral in order.referrals" v-bind:key="referral.referral_id">
                                <b-td>
                                    <a :href="getRoute('customers.detail', {id: referral.referral_id})" target="_blank">
                                        {{referral.user ? (referral.user.full_name ? referral.user.full_name : referral.user.login) : referral.referral_id}}
                                    </a>
                                </b-td>
                                <b-td>
                                    <p v-for="basketItem in referral.basketItems">
                                        <a :href="getRoute('products.detail', {id: basketItem.product.id})" target="_blank">
                                            {{basketItem.name}}
                                        </a>
                                    </p>
                                </b-td>
                                <b-td>
                                    <p v-for="promoCode in referral.promoCodes">
                                        <span :title="promoCode.name">{{promoCode.code}} (ID={{promoCode.promo_code_id}})</span>
                                    </p>
                                </b-td>
                            </b-tr>
                            <b-tr v-if="!order.referrals.length">
                                <b-td colspan="3">Реферальных партнеров нет</b-td>
                            </b-tr>
                        </b-tbody>
                    </b-table-simple>
                </b-col>
            </b-row>
            <b-row>
                <div class="col-sm-4">
                    <span class="font-weight-bold">Промокоды:</span>
                    <template v-for="(promoCode, key) in order.promoCodes">
                        <span v-if="key > 0">, </span><span :title="promoCode.name">{{promoCode.code}} (ID={{promoCode.promo_code_id}})</span>
                    </template>
                    <template v-if="!order.promoCodes.length">нет</template>
                </div>
            </b-row>
        </b-card>

        <b-card class="mt-4">
            <b-row>
                <div class="col-sm-6">
                    <p class="font-weight-bold">Получатель <span class="badge badge-warning" v-if="canEdit">(изменения применятся ко всем доставкам!)</span></p>
                </div>
                <div class="col-sm-6" v-if="canEdit">
                    <div class="float-right">
                        <button class="btn btn-success btn-sm" @click="save" :disabled="!$v.form.$anyDirty">
                            Сохранить
                        </button>
                        <button @click="cancel" class="btn btn-outline-danger btn-sm mr-1" :disabled="!$v.form.$anyDirty">
                            Отмена
                        </button>
                    </div>
                </div>
            </b-row>
            <b-row>
                <div class="col-sm-4">
                    <v-input v-model="$v.form.receiver_name.$model" :error="errorReceiverName" :disabled="!canEdit">
                        ФИО*
                    </v-input>
                </div>
                <div class="col-sm-4">
                    <v-input v-model="$v.form.receiver_phone.$model"
                             :error="errorReceiverPhone"
                             :placeholder="telPlaceholder"
                             autocomplete="off"
                             :disabled="!canEdit"
                             v-mask="telMask">
                        Телефон*
                    </v-input>
                </div>
                <div class="col-sm-4">
                    <v-input v-model="$v.form.receiver_email.$model"
                             :error="errorReceiverEmail"
                             :placeholder="emailPlaceholder"
                             :disabled="!canEdit"
                             autocomplete="off">
                        E-mail
                    </v-input>
                </div>
            </b-row>
            <template v-if="this.order.courierDelivery">
                <b-row>
                    <b-col>
                        <v-dadata v-model.sync="$v.form.delivery_address.address_string.$model"
                                  :error="errorDeliveryAddress"
                                  :disabled="!canEdit"
                                  @onSelect="onDeliveryAddressSelect">
                            Адрес до квартиры/офиса включительно*
                        </v-dadata>
                    </b-col>
                </b-row>
                <b-row>
                    <div class="col-sm-4">
                        <v-input v-model="$v.form.delivery_address.porch.$model" :disabled="!canEdit">
                            Подъезд
                        </v-input>
                    </div>
                    <div class="col-sm-4">
                        <v-input v-model="$v.form.delivery_address.floor.$model" :disabled="!canEdit">
                            Этаж
                        </v-input>
                    </div>
                    <div class="col-sm-4">
                        <v-input v-model="$v.form.delivery_address.intercom.$model" :disabled="!canEdit">
                            Домофон
                        </v-input>
                    </div>
                </b-row>
                <b-row>
                    <b-col>
                        <v-input v-model="$v.form.delivery_address.comment.$model" tag="textarea" :disabled="!canEdit">
                            Комментарий курьеру от клиента
                        </v-input>
                    </b-col>
                </b-row>
            </template>
            <b-row v-if="this.order.pickupDelivery">
                <b-col>
                    <f-custom-search-select
                            v-model="$v.form.point_id.$model"
                            :options="pointOptions"
                            @input="onChangePoint"
                            :isDisabled="!canEdit"
                    >Точка выдачи заказа*</f-custom-search-select>
                    <template v-if="points[selectedPointId]">
                        <p class="font-weight-bold">{{points[selectedPointId].type.name}} {{points[selectedPointId].name}}</p>
                        <p><span class="font-weight-bold">Адрес:</span> {{points[selectedPointId].address.address_string}}</p>
                        <p><span class="font-weight-bold">Телефон:</span> {{points[selectedPointId].phone}}</p>
                        <p><span class="font-weight-bold">График работы:</span> {{points[selectedPointId].timetable}}</p>
                        <p><span class="font-weight-bold">Способы оплаты:</span> {{points[selectedPointId].has_payment_card ? 'Наличные и банковские карты' : 'Только наличные'}}</p>
                    </template>
                    <template v-else>
                        <p class="font-weight-bold">{{this.model.pickupDelivery.point.type.name}} {{this.model.pickupDelivery.point.name}}</p>
                        <p><span class="font-weight-bold">Адрес:</span> {{this.model.pickupDelivery.point.address.address_string}}</p>
                        <p><span class="font-weight-bold">Телефон:</span> {{this.model.pickupDelivery.point.phone}}</p>
                        <p><span class="font-weight-bold">График работы:</span> {{this.model.pickupDelivery.point.timetable}}</p>
                        <p><span class="font-weight-bold">Способы оплаты:</span> {{this.model.pickupDelivery.point.has_payment_card ? 'Наличные и банковские карты' : 'Только наличные'}}</p>
                    </template>
                </b-col>
            </b-row>
            <b-row>
                <div class="col-sm-6">
                    <p class="font-weight-bold">Дополнительная информация</p>
                </div>
            </b-row>
            <b-row>
                <b-col>
                    <v-input v-model="$v.form.manager_comment.$model" tag="textarea" :disabled="!canEdit">
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
    import VDadata from '../../../../components/controls/VDaData/VDaData.vue';
    import FCustomSearchSelect from '../../../../components/filter/f-custom-search-select.vue';

    import {email, required, requiredIf} from 'vuelidate/lib/validators';
    import {validationMixin} from 'vuelidate';

    import {telMask} from '../../../../../scripts/mask';
    import {emailPlaceholder, telPlaceholder} from '../../../../../scripts/placeholder';

    export default {
        components: {
            VDadata,
            VInput,
            VSelect,
            FCustomSearchSelect
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
                        country_code: this.model.courierDelivery ? this.model.courierDelivery.delivery_address.country_code : '',
                        post_index: this.model.courierDelivery ? this.model.courierDelivery.delivery_address.post_index : '',
                        region: this.model.courierDelivery ? this.model.courierDelivery.delivery_address.region : '',
                        region_guid: this.model.courierDelivery ? this.model.courierDelivery.delivery_address.region_guid : '',
                        city: this.model.courierDelivery ? this.model.courierDelivery.delivery_address.city : '',
                        city_guid: this.model.courierDelivery ? this.model.courierDelivery.delivery_address.city_guid : '',
                        street: this.model.courierDelivery ? this.model.courierDelivery.delivery_address.street : '',
                        house: this.model.courierDelivery ? this.model.courierDelivery.delivery_address.house : '',
                        block: this.model.courierDelivery ? this.model.courierDelivery.delivery_address.block : '',
                        flat: this.model.courierDelivery ? this.model.courierDelivery.delivery_address.flat : '',
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
            const notRequired = {required: requiredIf(() => {return false;})};

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
                        street: notRequired,
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
                        flat: notRequired,
                        porch: notRequired,
                        floor: notRequired,
                        intercom: notRequired,
                        comment: notRequired,
                    },
                    point_id: requiredIfPickupDeliveryExist,
                    manager_comment: notRequired,
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
                this.$v.$touch();
            },
            save() {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }

                Services.showLoader();
                Services.net().put(this.getRoute('orders.detail.main.save', {id: this.order.id}), {}, this.form).then((data) => {
                    this.$set(this, 'order', data.order);
                    this.$set(this.order, 'shipments', data.order.shipments);

                    Services.msg("Изменения сохранены");
                }).finally(() => {
                    Services.hideLoader();
                });
            },
            cancel() {
                this.form.receiver_name = this.order.firstDelivery.receiver_name;
                this.form.receiver_phone = this.order.firstDelivery.receiver_phone;
                this.form.receiver_email = this.order.firstDelivery.receiver_email;
                if (this.model.courierDelivery) {
                    this.form.delivery_address.address_string = this.order.courierDelivery.delivery_address.address_string;
                    this.form.delivery_address.porch = this.order.courierDelivery.delivery_address.porch;
                    this.form.delivery_address.floor = this.order.courierDelivery.delivery_address.floor;
                    this.form.delivery_address.intercom = this.order.courierDelivery.delivery_address.intercom;
                    this.form.delivery_address.comment = this.order.courierDelivery.delivery_address.comment;
                }
                if (this.model.pickupDelivery) {
                    this.form.point_id = this.order.pickupDelivery.point_id;
                }
                this.form.manager_comment = this.order.manager_comment;
                this.$v.$reset();
            },
        },
        computed: {
            order: {
                get() {return this.model},
                set(value) {this.$emit('update:model', value)},
            },
            canEdit() {
                return this.order.status && this.order.status.id < this.orderStatuses.transferredToDelivery.id &&
                !this.order.is_canceled &&
                this.canUpdate(this.blocks.orders)
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
