<template>
    <transition name="modal">
        <modal :close="closeModal" v-if="isModalOpen('merchantEdit')">
            <div slot="header">
                Редактирование мерчанта
            </div>
            <div slot="body">
                <v-input v-model="$v.form.display_name.$model">
                    Название компании
                </v-input>
                <v-select v-model="$v.form.status.$model" :options="statusOptions">
                    Статус
                </v-select>

                <button @click="save" class="btn btn-dark">Сохранить</button>
            </div>
        </modal>
    </transition>
</template>

<script>
    import modal from '../../../../../components/controls/modal/modal.vue';
    import VInput from "../../../../../components/controls/VInput/VInput.vue";
    import VSelect from "../../../../../components/controls/VSelect/VSelect.vue";

    import modalMixin from '../../../../../mixins/modal.js';
    import {validationMixin} from 'vuelidate';
    import {required} from 'vuelidate/lib/validators';
    import Services from "../../../../../../scripts/services/services";
    import {mapGetters} from "vuex";

    export default {
        mixins: [modalMixin, validationMixin],
        components: {
            modal,
            VInput,
            VSelect
        },
        props: {
            source: Object,
            statuses: Object
        },
        data() {
            return {
                form: Object.assign({}, this.source),
            };
        },
        validations: {
            form: {
                display_name: {required},
                status: {required},
            }
        },
        methods: {
            save() {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }
                Services.net().post(this.getRoute('merchant.edit', {id: this.source.id}), {}, {
                    display_name: this.form.display_name,
                    status: this.form.status,
                })
                    .then((data)=> {
                        this.$emit('edited', data.merchant);
                        this.closeModal();
                    });
            }
        },
        computed: {
            ...mapGetters(['getRoute']),
            statusOptions() {
                return Object.values(this.statuses).map(status => ({value: status.id, text: status.name}));
            }
        },
        watch: {
            '$store.state.modal.currentModal': function(newValue) {
                if (newValue === 'columnsSelect') {
                    this.$set(this, 'form', Object.assign({}, this.source));
                }
            }
        }
    }
</script>
