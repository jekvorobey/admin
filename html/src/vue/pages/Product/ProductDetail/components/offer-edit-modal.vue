<template>
    <transition name="modal">
        <modal :close="closeModal" v-if="isModalOpen(modalName)">
            <div slot="header">
                {{ title }}
            </div>
            <div slot="body">
                <div class="row">
                    <f-select :options="saleOptions" v-model="offer.sale_status" class="col-md-6 col-sm-12">
                        Статус
                    </f-select>
                    <v-input v-model="offer.manual_sort" class="col-md-6 col-sm-12">
                        Сортировка
                    </v-input>
                </div>
                <button @click="save" class="btn btn-dark mt-3">Сохранить</button>
            </div>
        </modal>
    </transition>
</template>

<script>
import modal from '../../../../components/controls/modal/modal.vue';
import VInput from '../../../../components/controls/VInput/VInput.vue';
import modalMixin from '../../../../mixins/modal.js';
import Services from '../../../../../scripts/services/services';
import FSelect from '../../../../components/filter/f-select.vue';

export default {
        components: {
            FSelect,
            modal,
            VInput,
        },
        mixins: [modalMixin],
        props: {
            modalName: String,
            title: String,
            offer: Object,
            saleOptions: Array,
        },
        methods: {
            save() {
                this.closeModal();
                Services.showLoader();
                let data = {"props": {"status": this.offer.sale_status, "manual_sort" : this.offer.manual_sort}};
                Services.net().post(this.getRoute('offers.saveOfferProps', {id: this.offer.id}), {}, data).catch(() => {
                }).then(()=> {
                    Services.msg('Изменения сохранены');
                    this.$emit('onSave');
                }).finally(() => {
                  Services.hideLoader();
                });
            },
        },
    }
</script>

