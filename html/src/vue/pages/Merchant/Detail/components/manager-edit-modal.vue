<template>
    <transition name="modal">
        <modal :close="closeModal" v-if="isModalOpen('managerEdit')">
            <div slot="header">
                Редактирование менеджера мерчанта
            </div>
            <div slot="body">
                <v-select v-model="$v.form.managerId.$model" :options="managerOptions">
                    Менеджер
                </v-select>

                <button @click="save" class="btn btn-dark">Сохранить</button>
            </div>
        </modal>
    </transition>
</template>

<script>
    import modal from '../../../../components/controls/modal/modal.vue';
    import VSelect from '../../../../components/controls/VSelect/VSelect.vue';

    import modalMixin from '../../../../mixins/modal.js';
    import { validationMixin } from 'vuelidate';
    import { required } from 'vuelidate/lib/validators';

    import Services from '../../../../../scripts/services/services';

    export default {
        mixins: [modalMixin, validationMixin],
        components: {
            modal,
            VSelect
        },
        props: {
            source: Object,
        },
        data() {
            return {
                managers: [],
                form: {
                    managerId: this.source.manager_id
                }
            };
        },
        validations: {
            form: {
                managerId: {required}
            }
        },
        methods: {
            save() {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }
                Services.net().post(this.getRoute('merchant.edit', {id: this.source.id}), {}, {
                    manager_id: this.form.managerId,
                })
                    .then((data)=> {
                        let selectedManager = this.managers.filter(manager => manager.id === this.form.managerId);
                        this.$emit('edited', {
                            merchant: data.merchant,
                            manager: selectedManager[0]
                        });
                        this.closeModal();
                    });
            },
        },
        computed: {
            managerOptions() {
                return this.managers.map(manager => ({value: manager.id, text: manager.name}));
            }
        },
        watch: {
            '$store.state.modal.currentModal': function(newValue) {
                if (newValue === 'managerEdit') {
                    this.managerId = this.source.manager_id;
                    if (this.managers.length === 0) {
                        Services.net().get(this.getRoute('managers.all'))
                            .then(data => {
                                this.$set(this, 'managers', data.items);
                            })
                    }
                }
            }
        }
    }
</script>

<style scoped>

</style>