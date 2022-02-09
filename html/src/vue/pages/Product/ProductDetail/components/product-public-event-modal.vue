<template>
    <transition name="modal">
        <modal :close="closeModal" v-if="isModalOpen(modalName)" type="visible">
            <div slot="header">
                Привязать Мастер Классы
            </div>
            <div slot="body">
                <div class="row">
                    <f-multi-select :options="publicEventOptions" v-model="existingPublicEvents" class="col-md-12 col-sm-12">
                        Мастер класс
                    </f-multi-select>
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
import FMultiSelect from '../../../../components/filter/f-multi-select.vue';

export default {
    name: "product-public-event-modal",
    components: {
        FMultiSelect,
        modal
    },
    mixins: [modalMixin],
    props: {
        modalName: String,
        productId: Number,
        publicEvents: Array,
        allPublicEvents: Array,
    },
    data() {
        return {
            existingPublicEvents: this.publicEvents.map(type => type.id)
        }
    },
    methods: {
        save() {
            Services.net().put(this.getRoute('products.savePublicEvents', {id: this.productId}), null,
                {'public_events': this.existingPublicEvents.map(value => parseInt(value))})
                .then(result => {
                  this.$emit('onSave', result);
                  this.closeModal();
                })
        },
    },
    computed: {
        publicEventOptions() {
            return Object.values(this.allPublicEvents).map(type => ({value: type.id, text: type.name}));
        },
    }
}
</script>