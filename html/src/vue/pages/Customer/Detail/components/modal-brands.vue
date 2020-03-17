<template>
    <b-modal id="modal-brands" title="Редактирование личных брендов" hide-footer ref="modal">
        <template v-slot:default="{close}">

            <b-form-group>
                <b-form-checkbox
                    v-for="brand in sortBrands"
                    v-model="form.brands"
                    :key="brand.id"
                    :value="brand.id"
                >
                    {{ brand.name }}
                </b-form-checkbox>
            </b-form-group>

            <div class="float-right mt-3">
                <b-button @click="close()" variant="outline-primary">Отмена</b-button>
                <button class="btn btn-info" @click="saveBrands">Сохранить</button>
            </div>
        </template>
    </b-modal>
</template>

<script>
import Services from '../../../../../scripts/services/services.js';

export default {
    name: 'modal-brands',
    props: ['customerId', 'model', 'brands'],
    data() {
        return {
            form: {
                brands: [],
            }
        }
    },
    computed: {
        sortBrands() {
            return Object.values(this.brands).sort((a,b) => (a.name > b.name) ? 1 : ((b.name > a.name) ? -1 : 0));
        },
    },
    methods: {
        saveBrands() {
            Services.showLoader();
            Services.net().put(this.getRoute('customers.detail.preference.brand.save', {id: this.customerId}), null, {
                brands: this.form.brands
            }).then(data => {
                this.$emit('update:model', JSON.parse(JSON.stringify(this.form.brands)));
                Services.hideLoader();
                this.$bvModal.hide("modal-brands");
                Services.msg("Изменения сохранены");
            });
        },
    },
    mounted() {
        this.$refs.modal.$on('show', (bvModalEvt) => {
            this.form.brands = JSON.parse(JSON.stringify(this.model));
        })
    }
};
</script>

<style scoped>

</style>