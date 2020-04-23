<template>
    <transition name="modal">
        <modal :close="closeModal" v-if="isModalOpen(modalName)">
            <div slot="header">
                {{ title }}
            </div>
            <div slot="body">
                <div class="row">
                    <v-input v-model="offer.sale_status" class="col-md-6 col-sm-12">
                        Статус
                    </v-input>
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
    import Services from "../../../../../scripts/services/services";

    export default {
        components: {
            modal,
            VInput,
        },
        mixins: [modalMixin],
        props: {
            modalName: String,
            title: String,
            offer: Object,
        },
        methods: {
            save() {
                Services.showLoader();
                let data = {"props": {"status": this.offer.sale_status, "manual_sort" : this.offer.manual_sort}};
                Services.net().post(this.getRoute('offers.saveOfferProps', {id: this.offer.offer_id}), {}, data).catch(() => {
                }).then((response)=> {
                    Services.hideLoader();
                    this.$emit('onSave');
                    this.closeModal();
                });
            },
        },
    }
</script>

<style>
    .ck-editor__editable_inline {
        min-height: 300px;
    }
</style>
