<template>
    <transition name="modal">
        <modal :close="closeModal" v-if="isModalOpen('add-role-block-modal')">
            <div slot="header">
                {{source ? 'Редактирование блока' : 'Добавление блока'}}
            </div>
            <div slot="body">
                <v-select v-model="$v.form.block.$model" :options="blockOptions" :error="errorBlock">
                  Блок
                </v-select>
                <v-select v-model="$v.form.permission.$model" :options="permissionOptions" :error="errorPermission">
                  Разрешение
                </v-select>
                <button @click="save" class="btn btn-dark">Сохранить</button>
            </div>
        </modal>
    </transition>
</template>

<script>
    import modal from '../../../components/controls/modal/modal.vue';
    import VSelect from '../../../components/controls/VSelect/VSelect.vue';

    import modalMixin from '../../../mixins/modal.js';
    import { validationMixin } from 'vuelidate';
    import { required } from 'vuelidate/lib/validators';
    import Services from '../../../../scripts/services/services';

    export default {
        mixins: [modalMixin, validationMixin],
        components: {
            VSelect,
            modal
        },
        props: {
            source: Object,
            blocks: Object,
            permissions: Object,
        },
        data() {
            return {
                form: {
                    block: '',
                    permission: '',
                }
            };
        },
        validations() {
            let validations = {
                form: {
                    block: {required},
                    permission: {required},
                }
            };
            if (!this.source) {
                validations.form.block[required] = required;
                validations.form.permission[required] = required;
            }
            console.log(validations);
            return validations;
        },
        methods: {
            save() {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }
                let formData = {
                    block: this.form.block,
                    permission: this.form.permission,
                };
                if (this.source) {
                    formData.id = this.source.id;
                }
                Services.net().post(this.getRoute('role.addBlock'), {}, formData).then(() => {
                    this.$emit('onSave', {block_id: this.form.name, permission_id: this.form.front});
                });
            },
        },
        computed: {
            blockOptions() {
                return Object.values(this.blocks).map(block => ({value: block.id, text: block.name}));
            },
            permissionOptions() {
              return Object.values(this.permissions).map(permission => ({value: permission.id, text: permission.name}));
            },
            // =========================================================================================================
            errorBlock() {
                if (this.$v.form.block.$dirty) {
                    if (!this.$v.form.block.required) return 'Обязательное поле!';
                }
            },
            errorPermission() {
                if (this.$v.form.permission.$dirty) {
                    if (!this.$v.form.permission.required) return 'Обязательное поле!';
                }
            },
        },
        watch: {
            '$store.state.modal.currentModal': function(newValue) {
                if (newValue === 'roleAdd' && this.source) {
                    this.form.block = this.source.block;
                    this.form.permission = this.source.permission;
                }
            }
        }
    }
</script>