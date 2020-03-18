<template>
    <table class="table">
        <thead>
            <tr>
                <th>
                    Товар
                    <div class="custom-control custom-switch d-inline-block">
                        <input type="checkbox" class="custom-control-input" id="active" v-model="filter.active">
                        <label class="custom-control-label" for="active">Показывать активные</label>
                    </div>
                </th>
                <th>Описание</th>
                <th>Файлы</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="promoProduct in promoProducts" v-if="filter.active === !!promoProduct.active">
                <td>
                    {{ promoProduct.product_id }}
                </td>
                <td>
                    <textarea class="form-control" v-model="promoProduct.description"/>
                </td>
                <td>
                    <div v-for="(file, i) in promoProduct.files" class="mb-1">
                        <img :src="media.file(file)" style="max-width: 150px;"/>
                        <v-delete-button btn-class="btn-danger btn-sm" @delete="$delete(promoProduct.files, i)"/>
                    </div>

                    <file-input @uploaded="(data) => $set(promoProduct.files, promoProduct.files.length, data.id)" class="mb-3"></file-input>
                </td>
                <td>
                    <button class="btn btn-success btn-sm" @click="savePromoProduct(promoProduct)"><fa-icon icon="save"/></button>
                    <button class="btn btn-danger btn-sm" @click="archivePromoProduct(promoProduct)"><fa-icon icon="file-archive"/></button>
                </td>
            </tr>
            <tr v-if="filter.active">
                <td>
                    <input class="form-control form-control-sm" v-model="newPromoProduct.product_id"/>
                </td>
                <td>
                    <textarea class="form-control" v-model="newPromoProduct.description"/>
                </td>
                <td>
                    <div v-for="(file, i) in newPromoProduct.files" class="mb-1">
                        <img :src="media.file(file)" style="max-width: 150px;"/>
                        <v-delete-button btn-class="btn-danger btn-sm" @delete="$delete(newPromoProduct.files, i)"/>
                    </div>

                    <file-input @uploaded="(data) => $set(newPromoProduct.files, newPromoProduct.files.length, data.id)" class="mb-3"></file-input>
                </td>
                <td>
                    <button class="btn btn-success btn-sm" @click="savePromoProduct(newPromoProduct)"><fa-icon icon="plus"/></button>
                </td>
            </tr>
        </tbody>
    </table>
</template>

<script>
import Services from '../../../../../scripts/services/services.js';
import VDeleteButton from '../../../../components/controls/VDeleteButton/VDeleteButton.vue';
import FileInput from '../../../../components/controls/FileInput/FileInput.vue';

export default {
    name: 'tab-promo-product',
    components: {FileInput, VDeleteButton},
    props: ['id'],
    data() {
        return {
            promoProducts: [],
            newPromoProduct: {
                product_id: '',
                description: '',
                active: 1,
                files: [],
            },
            filter: {
                active: true,
            }
        }
    },
    methods: {
        savePromoProduct(promoProduct) {
            Services.showLoader();
            Services.net().put(this.getRoute('customers.detail.promoProduct.save', {id: this.id}), promoProduct).then(data => {
                this.promoProducts = data.promoProducts;
                this.newPromoProduct.product_id = '';
                this.newPromoProduct.description = '';
                this.newPromoProduct.files = [];
            }).finally(() => {
                Services.hideLoader();
            })
        },
        archivePromoProduct(promoProduct) {
            promoProduct.active = 0;
            this.savePromoProduct(promoProduct);
        },
    },
    created() {
        Services.showLoader();
        Services.net().get(this.getRoute('customers.detail.promoProduct', {id: this.id})).then(data => {
            this.promoProducts = data.promoProducts;
        }).finally(() => {
            Services.hideLoader();
        })
    }
};
</script>

<style scoped>

</style>