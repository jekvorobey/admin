<template>
    <transition name="modal">
        <modal :close="closeModal" v-if="isModalOpen(modalName)">
            <div slot="header">
                Изменить статус товара
            </div>
            <div slot="body">
                <div class="row">
                    <f-select :options="this.statuses" v-model="currentStatus" class="col-md-6 col-sm-12">
                        Статус
                    </f-select>
                </div>
                <button @click="save" class="btn btn-dark mt-3">Сохранить</button>
            </div>
        </modal>
    </transition>
</template>

<script>
    import modal from '../../../../components/controls/modal/modal.vue';
    import modalMixin from '../../../../mixins/modal.js';
    import Services from "../../../../../scripts/services/services";
    import FSelect from "../../../../components/filter/f-select.vue";

    export default {
        components: {
            FSelect,
            modal,
        },
        mixins: [modalMixin],
        props: {
            modalName: String,
            productId: Number,
            approvalOptions: Object,
            currentStatus: Number,
        },
        methods: {
            save() {
                Services.net().put(this.getRoute('products.changeApproveStatus', {id: this.productId}), null,
                     {'approval_status': parseInt(this.currentStatus)})
                    .then(result => {
                        this.$emit('onSave', result);
                        this.closeModal();
                })
            },
        },
        computed: {
            statuses() {
                let result = [];
                Object.entries(this.approvalOptions).map(([key, value]) =>
                    result.push({value: parseInt(key), text: value})
                );
                return result;
            },
        }
    }
</script>
