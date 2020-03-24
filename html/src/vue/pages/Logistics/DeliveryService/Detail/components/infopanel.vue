<template>
    <div>
        <div class="row">
            Инфопанель
            <button class="btn btn-success btn-sm" @click="saveDeliveryService" :disabled="!$v.form.$anyDirty">
                Сохранить
            </button>
            <button @click="cancel" class="btn btn-outline-danger btn-sm" :disabled="!$v.form.$anyDirty">Отмена</button>
        </div>
        <div class="row">
            <v-input v-model="$v.form.name.$model" :error="errorName" class="col-md-6 col-sm-12">
                Название
            </v-input>
        </div>
        <div class="row">
            <v-date v-model="$v.form.registered_at.$model" :error="errorRegisteredAt" class="col-md-6 col-sm-12">
                Дата регистрации
            </v-date>
        </div>
        <div class="row">
            <v-select v-model="$v.form.status.$model" :options="deliveryServiceStatusOptions"
                    class="col-md-6 col-sm-12">
                Статус
            </v-select>
        </div>
        <div class="row">
            <v-input v-model="$v.form.priority.$model" :error="errorPriority" class="col-md-6 col-sm-12">
                Приоритет
            </v-input>
        </div>
    </div>
</template>

<script>
    import Services from '../../../../../../scripts/services/services.js';
    import FileInput from '../../../../../components/controls/FileInput/FileInput.vue';
    import VDeleteButton from '../../../../../components/controls/VDeleteButton/VDeleteButton.vue';
    import VInput from '../../../../../components/controls/VInput/VInput.vue';
    import VDate from '../../../../../components/controls/VDate/VDate.vue';
    import VSelect from '../../../../../components/controls/VSelect/VSelect.vue';

    import {validationMixin} from 'vuelidate';
    import {integer, minValue, required} from 'vuelidate/lib/validators';

    export default {
    name: 'infopanel',
    components: {
        VDeleteButton,
        FileInput,
        VInput,
        VSelect,
        VDate,
    },
    mixins: [
        validationMixin,
    ],
    props: [
        'model',
        'deliveryServiceStatuses',
    ],
    data() {
        return {
            editStatus: false,
            form: Object.assign({}, this.model),
        };
    },
    validations: {
        form: {
            name: {required},
            registered_at: {required},
            priority: {required, integer, minValue: minValue(1)},
        }
    },
    watch: {
        'model.status': function (val, oldVal) {
            this.form.status = this.model.status;
        }
    },
    methods: {
        saveDeliveryService() {
            Services.showLoader();
            Services.net().put(this.getRoute('deliveryService.detail.save', {id: this.deliveryService.id}), {}, this.form).then(() => {
                this.deliveryService.name = this.form.name;
                this.deliveryService.registered_at = this.form.registered_at;
                this.deliveryService.status = this.form.status;
                this.deliveryService.priority = this.form.priority;

                Services.msg("Изменения сохранены");
            }).finally(data => {
                Services.hideLoader();
            })
        },
        cancel() {
            this.form.name = this.deliveryService.name;
            this.form.registered_at = this.deliveryService.last_name;
            this.form.status = this.deliveryService.status;
            this.form.priority = this.deliveryService.priority;
        },
    },
    computed: {
        deliveryService: {
            get() {return this.model},
            set(value) {this.$emit('update:model', value)},
        },
        deliveryServiceStatusOptions() {
            return Object.values(this.deliveryServiceStatuses).map(status => ({
                value: status.id,
                text: status.name
            }));
        },
        errorName() {
            if (this.$v.form.name.$dirty) {
                if (!this.$v.form.name.required) {
                    return "Обязательное поле!";
                }
            }
        },
        errorRegisteredAt() {
            if (this.$v.form.registered_at.$dirty) {
                if (!this.$v.form.registered_at.required) {
                    return "Обязательное поле!";
                }
            }
        },
        errorPriority() {
            if (this.$v.form.priority.$dirty) {
                if (!this.$v.form.priority.required) {
                    return "Обязательное поле!";
                }
                if (!this.$v.form.priority.integer || !this.$v.form.priority.minValue) {
                    return "Только целое число больше 0";
                }
            }
        },
    },
};
</script>
