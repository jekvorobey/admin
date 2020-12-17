<template>
    <table class="table table-sm">
    <tbody>
        <tr v-if="merchantVat">
            <td>Налоги мерчанта</td>
            <td colspan="3">
                <div class="input-group input-group-sm">
                    <input class="form-control form-control-sm" v-model="merchantVat.value">
                    <div class="input-group-prepend">
                        <span class="input-group-text">%</span>
                    </div>
                </div>
            </td>
            <td>
                <button class="btn btn-sm btn-success" @click="saveMerchantVat"><fa-icon icon="save"/></button>
                <v-delete-button @delete="removeVat(merchantVat.id)" btn-class="btn-danger btn-sm" v-if="merchantVat.id"/>
            </td>
        </tr>
        <tr>
            <th>Тип</th>
            <th style="width: 10%">НДС</th>
            <th style="width: 35%">Бренд/Категория/Товар</th>
            <th></th>
        </tr>
        <tr v-for="vat in vats">
            <td>{{ typeName(vat.type) }}</td>
            <td>
                <div class="input-group input-group-sm">
                    <input class="form-control" v-model="vat.value">
                    <div class="input-group-prepend">
                        <span class="input-group-text">%</span>
                    </div>
                </div>
            </td>
            <td>
                {{ relatedNameByType(vat.type) }}:
                {{ relatedValueByType(vat.type, vat.related_id) }}
            </td>

            <td>
                <button class="btn btn-sm btn-success" @click="saveVat(vat)"><fa-icon icon="save"/></button>
                <v-delete-button @delete="removeVat(vat.id)" btn-class="btn-danger btn-sm"/>
            </td>
        </tr>
        <tr>
            <td>
                <select v-model="newVat.type" class="form-control form-control-sm" @change="newVat.related_id = null">
                    <option :value="null">Выберите тип</option>
                    <option :value="merchantVatTypes.brand">{{ typeName(merchantVatTypes.brand) }}</option>
                    <option :value="merchantVatTypes.category">{{ typeName(merchantVatTypes.category) }}</option>
                    <option :value="merchantVatTypes.sku">{{ typeName(merchantVatTypes.sku) }}</option>
                </select>
            </td>
            <td>
                <div class="input-group input-group-sm">
                    <input class="form-control" v-model="newVat.value">
                    <div class="input-group-prepend">
                        <span class="input-group-text">%</span>
                    </div>
                </div>
            </td>
            <td>
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend" v-if="newVat.type">
                        <span class="input-group-text">{{ relatedNameByType(newVat.type) }}:</span>
                    </div>
                    <input v-if="!newVat.type || newVat.type === merchantVatTypes.sku"
                           v-model="newVat.related_id"
                           class="form-control form-control-sm">
                    <select v-else
                            v-model="newVat.related_id"
                            class="form-control form-control-sm">
                        <option
                            v-for="option in relatedOptionsByType(newVat.type)"
                            :value="option.value">
                          {{ option.text }}
                        </option>
                    </select>
                </div>
            </td>

            <td><button class="btn btn-sm btn-outline-success" @click="saveVat(newVat)"><fa-icon icon="plus"/></button></td>
        </tr>
    </tbody>
    </table>
</template>

<script>
import Services from '../../../../../scripts/services/services.js';
import DatePicker from 'vue2-datepicker';
import 'vue2-datepicker/index.css';
import 'vue2-datepicker/locale/ru.js';
import VDeleteButton from '../../../../components/controls/VDeleteButton/VDeleteButton.vue';

export default {
    name: 'tab-taxes',
    props: ['id', 'brandList', 'categoryList'],
    components: {VDeleteButton, DatePicker},
    data() {
        return {
            vats: [],
            merchantVat: null,
            newVat: {
                type: null,
                value: null,
                related_id: null,
            },
            brands: {},
            categories: {},
            products: {},
        }
    },
    methods: {
        saveVat(vat) {
            Services.showLoader();
            Services.net().post(this.getRoute('merchant.detail.vat.save', {id: this.id}), {}, {
                id: vat.id,
                type: vat.type,
                value:vat.value,
                related_id: vat.related_id,
            }).then((data) => {
                this.vats = data.vats;
                this.brands = data.brands;
                this.categories = data.categories;
                this.products = data.products;
                this.newVat.type = null;
                this.newVat.value = null;
                this.newVat.related_id = null;
            }).finally(() => {
                Services.hideLoader();
            })
        },
        saveMerchantVat() {
            Services.showLoader();
            Services.net().post(this.getRoute('merchant.detail.vat.save', {id: this.id}), {}, {
                type: this.merchantVatTypes.merchant,
                id: this.merchantVat.id,
                value: this.merchantVat.value,
            }).then((data) => {
                this.merchantVat = data.merchantVat;
            }).finally(() => {
                Services.hideLoader();
            })
        },
        removeVat(id) {
            Services.showLoader();
            Services.net().post(this.getRoute('merchant.detail.vat.remove', {id: this.id}), {}, {
                id: id,
            }).then((data) => {
                this.commissions = data.commissions;
                this.merchantVat = data.merchantVat;
                this.brands = data.brands;
                this.categories = data.categories;
                this.products = data.products;
            }).finally(() => {
                Services.hideLoader();
            })
        },
        typeName(type) {
            switch (type) {
                case this.merchantVatTypes.brand:
                    return 'За бренд';
                case this.merchantVatTypes.category:
                    return 'За категорию';
                case this.merchantVatTypes.sku:
                    return 'За товар';
            }
        },
        relatedNameByType(type) {
            switch (type) {
                case this.merchantVatTypes.brand:
                    return 'Бренд';
                case this.merchantVatTypes.category:
                    return 'Категория';
                case this.merchantVatTypes.sku:
                    return 'Товар';
            }
        },
        relatedValueByType(type, related_id) {
            switch (type) {
                case this.merchantVatTypes.brand:
                    return this.brands[related_id] || related_id;
                case this.merchantVatTypes.category:
                    return this.categories[related_id] || related_id;
                case this.merchantVatTypes.sku:
                    return this.products[related_id] || related_id;
            }
        },
        relatedOptionsByType(type) {
            switch (type) {
                case this.merchantVatTypes.brand:
                    return Object.values(this.brandList).map(brand => {
                        return { value: brand.id, text: brand.name }
                    });
                case this.merchantVatTypes.category:
                  return Object.values(this.categoryList).map(category => {
                    return { value: category.id, text: category.name }
                  });
            }
        },
    },
    created() {
        Services.showLoader();
        Services.net().get(this.getRoute('merchant.detail.vat', {id: this.id})).then(data => {
            this.vats = data.vats;
            this.merchantVat = data.merchantVat;
            this.brands = data.brands;
            this.categories = data.categories;
            this.products = data.products;
        }).finally(() => {
            Services.hideLoader();
        })
    }
};
</script>
