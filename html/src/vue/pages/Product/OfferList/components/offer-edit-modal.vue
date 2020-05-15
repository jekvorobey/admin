<template>
    <transition name="modal">
        <modal :close="closeModal" v-if="isModalOpen(modalName)">
            <div slot="header">
                <strong>{{ offer.productName }}</strong>
            </div>
            <div slot="body">
                <div class="row">
                    <f-select @change="activateSaveButton()"
                            :options="saleOptions"
                            v-model="offer.sale_status"
                            class="col-md-6 col-sm-12"
                    >Статус</f-select>
                </div>
                <button id="save-button" @click="save" class="btn btn-dark mt-3" disabled>Сохранить</button>
            </div>
        </modal>
    </transition>
</template>

<script>
    import modal from '../../../../components/controls/modal/modal.vue';
    import VInput from '../../../../components/controls/VInput/VInput.vue';
    import modalMixin from '../../../../mixins/modal.js';
    import Services from "../../../../../scripts/services/services";
    import FSelect from '../../../../components/filter/f-select.vue';

    export default {
        components: {
            modal,
            VInput,
            FSelect,
        },
        mixins: [modalMixin],
        props: {
            modalName: String,
            offer: Object,
        },
        methods: {
            save() {
                this.closeModal();
                Services.showLoader();
                let data = {"offer_ids": [this.offer.id], "sale_status": this.offer.sale_status};
                Services.net().put(this.getRoute('offers.change.saleStatus'), {}, data).catch(() => {
                }).then(() => {
                    Services.hideLoader();
                });
            },
            activateSaveButton() {
                document.getElementById('save-button').disabled = false;
            },
        },
        computed: {
            saleOptions() {
                return [
                    {value: 1, text: 'В продаже'},
                    {value: 2, text: 'Предзаказ'},
                    {value: 3, text: 'Снято с продажи'},
                    {value: 4, text: 'Доступен к продаже'},
                    {value: 5, text: 'Недоступен к продаже'},
                ];
            },
        }
    }
</script>

<style>
    .ck-editor__editable_inline {
        min-height: 300px;
    }
</style>
