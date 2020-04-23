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
            </div>
        </modal>
    </transition>
</template>

<script>
    import modal from '../../../../components/controls/modal/modal.vue';
    import VInput from '../../../../components/controls/VInput/VInput.vue';
    import modalMixin from '../../../../mixins/modal.js';

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
        },
    }
</script>

<style>
    .ck-editor__editable_inline {
        min-height: 300px;
    }
</style>
