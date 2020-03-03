<template>
    <b-modal id="modal-categories" title="Редактирование личных брендов" hide-footer ref="modal">
        <template v-slot:default="{close}">

            <modal-categories-checkbox :categories="formCategories.children" :model.sync="form.categories"/>

            <div class="float-right mt-3">
                <b-button @click="close()" variant="outline-primary">Отмена</b-button>
                <button class="btn btn-info" @click="saveCategories">Сохранить</button>
            </div>
        </template>
    </b-modal>
</template>

<script>
import Services from '../../../../../scripts/services/services.js';
import { mapGetters } from 'vuex';
import NestedSets from '../../../../../scripts/nestedSets.js';
import ModalCategoriesCheckbox from './modal-categories-checkbox.vue';

export default {
    name: 'modal-categories',
    components: {ModalCategoriesCheckbox},
    props: ['customerId', 'model', 'categories'],
    data() {
        return {
            form: {
                categories: [],
            }
        }
    },
    computed: {
        ...mapGetters(['getRoute']),
        formCategories() {
            return NestedSets.process(Object.values(this.categories));
        },
    },
    methods: {
        saveCategories() {
            Services.showLoader();
            Services.net().put(this.getRoute('customers.detail.category.save', {id: this.customerId}), null, {
                categories: this.form.categories
            }).then(data => {
                this.$emit('update:model', JSON.parse(JSON.stringify(this.form.categories)));
                Services.hideLoader();
                this.$bvModal.hide("modal-categories");
                Services.msg("Изменения сохранены");
            });
        },
    },
    mounted() {
        this.$refs.modal.$on('show', (bvModalEvt) => {
            this.form.categories = JSON.parse(JSON.stringify(this.model));
        })
    }
};
</script>

<style scoped>

</style>