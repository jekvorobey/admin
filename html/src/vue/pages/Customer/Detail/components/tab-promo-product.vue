<template>
    <table class="table">
        <thead>
            <tr>
                <th>Товар</th>
                <th>Описание</th>
                <th>Файлы</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="promoProduct in promoProducts">
                <td>
                    <input class="form-control form-control-sm" v-model="promoProduct.product_id"/>
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
        }
    },
    methods: {
        savePromoProduct(promoProduct) {
            Services.showLoader();
            Services.net().put(this.getRoute('customers.detail.promoProduct.save', {id: this.id}), promoProduct).then(data => {
                this.promoProducts = data.promoProducts;
                Services.hideLoader();
            })
        }
    },
    created() {
        Services.showLoader();
        Services.net().get(this.getRoute('customers.detail.promoProduct', {id: this.id})).then(data => {
            this.promoProducts = data.promoProducts;
            Services.hideLoader();
        })
    }
};
</script>

<style scoped>

</style>