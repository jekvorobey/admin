<template>
    <transition name="modal">
        <modal :close="closeModal" type="wide" v-if="isModalOpen(modalName)">
            <div slot="header">
                Редактирование состава продукта
            </div>
            <div slot="body">
                <textarea v-model="items"
                          class="form-control" rows="14">
                </textarea>
                <button @click="save"
                        class="btn btn-dark mt-3">
                    Сохранить
                </button>
            </div>
        </modal>
    </transition>
</template>

<script>

import Services from '../../../../../scripts/services/services';

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
        ingredients: String||null,
        productId: Number
    },
    data() {
        return {
            items: this.ingredients
        }
    },
    methods: {
        prepareItems() {
            return this.items && this.items.length > 0 ?
                JSON.stringify(this.items.split(', '))
                : null;
        },
        save() {
            Services.showLoader();
            Services.net().put(this.getRoute('products.saveIngredients', {id: this.productId}),
                {
                    items: this.prepareItems()
                })
                .then(()=> {
                this.closeModal();
                Services.msg('Состав успешно обновлен');
                    window.location.href = this.getRoute('products.detail', {id: this.productId})
            }, () => {
                    Services.msg('Не удалось сохранить изменения')
                })
        },
    },
}
</script>

<style>

</style>