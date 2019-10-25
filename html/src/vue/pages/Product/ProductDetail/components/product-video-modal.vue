<template>
    <transition name="modal">
        <modal :close="closeModal" v-if="isModalOpen(modalName)">
            <div slot="header">
                Редактирование Товара
            </div>
            <div slot="body">
                <v-input v-model="$v.form.video.$model">
                    Код ролика на YouTube
                </v-input>
                <button @click="save" class="btn btn-dark" :disabled="!$v.form.$anyDirty">Сохранить</button>
            </div>
        </modal>
    </transition>
</template>

<script>
    import {validationMixin} from 'vuelidate';
    import {required} from 'vuelidate/lib/validators';

    import Helpers from '../../../../../scripts/helpers';
    import Services from "../../../../../scripts/services/services";
    import {mapGetters} from "vuex";

    import modal from '../../../../components/controls/modal/modal.vue';

    import VInput from "../../../../components/controls/VInput/VInput.vue";
    import VSelect from "../../../../components/controls/VSelect/VSelect.vue";

    import modalMixin from '../../../../mixins/modal.js';


    const formFields = ['video'];
    export default {
        components: {
            modal,
            VInput,
            VSelect,
        },
        mixins: [modalMixin, validationMixin],
        props: {
            modalName: String,
            source: Object,
        },
        data () {
            return {
                form: Object.assign({}, this.source),
            };
        },
        validations: {
            form: {
                video: {},
            }
        },
        methods: {
            save() {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }
                let data = Helpers.filterObject(this.form, formFields);
                Services.net().post(this.getRoute('products.saveProduct', {id: this.source.id}), {}, data)
                    .then(()=> {
                        this.$emit('onSave');
                        this.closeModal();
                    });
            }
        },
        computed: {
            ...mapGetters(['getRoute']),
        }
    }
</script>

<style scoped>

</style>