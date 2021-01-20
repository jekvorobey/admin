<template>
    <table class="table table-sm">
    <tbody>
        <tr v-if="merchantCommission">
            <td>Комиссия мерчанта</td>
            <td colspan="3">
                <div class="input-group input-group-sm">
                    <input class="form-control form-control-sm" v-model="merchantCommission.value">
                    <div class="input-group-prepend">
                        <span class="input-group-text">%</span>
                    </div>
                </div>
            </td>
            <td>
                <button class="btn btn-sm btn-success" @click="saveMerchantCommission"><fa-icon icon="save"/></button>
                <v-delete-button @delete="removeCommission(merchantCommission.id)" btn-class="btn-danger btn-sm" v-if="merchantCommission.id"/>
            </td>
        </tr>
        <tr>
            <th>Тип</th>
            <th style="width: 10%">Комиссия</th>
            <th style="width: 35%">Бренд/Категория/Товар</th>
            <th>Даты активности</th>
            <th></th>
        </tr>
        <tr v-for="commission in commissions">
            <td>{{ typeName(commission.type) }}</td>
            <td>
                <div class="input-group input-group-sm">
                    <input class="form-control" v-model="commission.value">
                    <div class="input-group-prepend">
                        <span class="input-group-text">%</span>
                    </div>
                </div>
            </td>
            <td>
                {{ relatedNameByType(commission.type) }}:
                {{ relatedValueByType(commission.type, commission.related_id) }}
            </td>
            <td>
                <date-picker
                    class="w-100"
                    v-model="commission.dates"
                    value-type="format"
                    format="YYYY-MM-DD"
                    range
                    input-class="form-control form-control-sm"
                />
            </td>
            <td>
                <button class="btn btn-sm btn-success" @click="saveCommission(commission)"><fa-icon icon="save"/></button>
                <v-delete-button @delete="removeCommission(commission.id)" btn-class="btn-danger btn-sm"/>
            </td>
        </tr>
        <tr>
            <td>
                <select v-model="newCommission.type" class="form-control form-control-sm" @change="newCommission.related_id = null">
                    <option :value="null">Выберите тип</option>
                    <option :value="merchantCommissionTypes.brand">{{ typeName(merchantCommissionTypes.brand) }}</option>
                    <option :value="merchantCommissionTypes.category">{{ typeName(merchantCommissionTypes.category) }}</option>
                    <option :value="merchantCommissionTypes.sku">{{ typeName(merchantCommissionTypes.sku) }}</option>
                </select>
            </td>
            <td>
                <div class="input-group input-group-sm">
                    <input class="form-control" v-model="newCommission.value">
                    <div class="input-group-prepend">
                        <span class="input-group-text">%</span>
                    </div>
                </div>
            </td>
            <td>
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend" v-if="newCommission.type">
                        <span class="input-group-text">{{ relatedNameByType(newCommission.type) }}:</span>
                    </div>
                    <input v-if="!newCommission.type || newCommission.type === merchantCommissionTypes.sku"
                           v-model="newCommission.related_id"
                           class="form-control form-control-sm">
                    <select v-else
                            v-model="newCommission.related_id"
                            class="form-control form-control-sm">
                        <option
                            v-for="option in relatedOptionsByType(newCommission.type)"
                            :value="option.value">
                          {{ option.text }}
                        </option>
                    </select>
                </div>
            </td>
            <td>
                <date-picker
                    class="w-100"
                    v-model="newCommission.dates"
                    value-type="format"
                    format="YYYY-MM-DD"
                    range
                    input-class="form-control form-control-sm"
                />
            </td>
            <td><button class="btn btn-sm btn-outline-success" @click="saveCommission(newCommission)"><fa-icon icon="plus"/></button></td>
        </tr>
    </tbody>
    </table>
</template>

<script>
import Services from '../../../../../scripts/services/services.js';
import DatePicker from 'vue2-datepicker';
import VSelect from '../../../../components/controls/VSelect/VSelect.vue';
import 'vue2-datepicker/index.css';
import 'vue2-datepicker/locale/ru.js';
import VDeleteButton from '../../../../components/controls/VDeleteButton/VDeleteButton.vue';

const defaultOptions = [{ value: null, text: 'Нет' }];
export default {
    name: 'tab-commission',
    props: ['id', 'brandList', 'categoryList'],
    components: {VDeleteButton, DatePicker, VSelect},
    data() {
        return {
            commissions: [],
            merchantCommission: null,
            newCommission: {
                type: null,
                value: null,
                related_id: null,
                dates: null,
            },
            brands: {},
            categories: {},
            products: {},
        }
    },
    methods: {
        saveCommission(commission) {
            Services.showLoader();
            Services.net().post(this.getRoute('merchant.detail.commission.save', {id: this.id}), {}, {
                id: commission.id,
                type: commission.type,
                value:commission.value,
                related_id: commission.related_id,
                dates: commission.dates,
            }).then((data) => {
                this.commissions = data.commissions;
                this.brands = data.brands;
                this.categories = data.categories;
                this.products = data.products;
                this.newCommission.type = null;
                this.newCommission.value = null;
                this.newCommission.related_id = null;
                this.newCommission.dates = null;
            }).finally(() => {
                Services.hideLoader();
            })
        },
        saveMerchantCommission() {
            Services.showLoader();
            Services.net().post(this.getRoute('merchant.detail.commission.save', {id: this.id}), {}, {
                type: this.merchantCommissionTypes.merchant,
                id: this.merchantCommission.id,
                value: this.merchantCommission.value,
            }).then((data) => {
                this.merchantCommission = data.merchantCommission;
            }).finally(() => {
                Services.hideLoader();
            })
        },
        removeCommission(id) {
            Services.showLoader();
            Services.net().post(this.getRoute('merchant.detail.commission.remove', {id: this.id}), {}, {
                id: id,
            }).then((data) => {
                this.commissions = data.commissions;
                this.merchantCommission = data.merchantCommission;
                this.brands = data.brands;
                this.categories = data.categories;
                this.products = data.products;
            }).finally(() => {
                Services.hideLoader();
            })
        },
        typeName(type) {
            switch (type) {
                case this.merchantCommissionTypes.brand:
                    return 'За бренд';
                case this.merchantCommissionTypes.category:
                    return 'За категория';
                case this.merchantCommissionTypes.sku:
                    return 'За товар';
            }
        },
        relatedNameByType(type) {
            switch (type) {
                case this.merchantCommissionTypes.brand:
                    return 'Бренд';
                case this.merchantCommissionTypes.category:
                    return 'Категория';
                case this.merchantCommissionTypes.sku:
                    return 'Товар';
            }
        },
        relatedValueByType(type, related_id) {
            switch (type) {
                case this.merchantCommissionTypes.brand:
                    return this.brands[related_id] || related_id;
                case this.merchantCommissionTypes.category:
                    return this.categories[related_id] || related_id;
                case this.merchantCommissionTypes.sku:
                    return this.products[related_id] || related_id;
            }
        },
        relatedOptionsByType(type) {
            switch (type) {
                case this.merchantCommissionTypes.brand:
                    return Object.values(this.brandList).map(brand => {
                        return { value: brand.id, text: brand.name }
                    });
                case this.merchantCommissionTypes.category:
                  return Object.values(this.categoryList).map(category => {
                    return { value: category.id, text: category.name }
                  });
            }
        },
    },
    created() {
      console.log(this.brandList);
        Services.showLoader();
        Services.net().get(this.getRoute('merchant.detail.commission', {id: this.id})).then(data => {
            this.commissions = data.commissions;
            this.merchantCommission = data.merchantCommission;
            this.brands = data.brands;
            this.categories = data.categories;
            this.products = data.products;
        }).finally(() => {
            Services.hideLoader();
        })
    }
};
</script>

<style scoped>

</style>