<template>
    <b-card>
        <b-row>
            <b-col>
                <p class="font-weight-bold">Инфопанель</p>
            </b-col>
            <b-col v-if="canUpdate(blocks.logistics)">
                <div class="float-right">
                    <button class="btn btn-success btn-sm" @click="save" :disabled="!$v.form.$anyDirty">
                        Сохранить
                    </button>
                    <button @click="cancel" class="btn btn-outline-danger btn-sm mr-1" :disabled="!$v.form.$anyDirty">
                        Отмена
                    </button>
                </div>
            </b-col>
        </b-row>

        <b-row>
            <b-col>
                <v-input v-model="$v.form.name.$model" :error="errorName">
                    Название
                </v-input>
            </b-col>
            <b-col>
                <v-date v-model="$v.form.registered_at.$model" :error="errorRegisteredAt">
                    Дата регистрации
                </v-date>
            </b-col>
        </b-row>
        <b-row>
            <b-col>
                <v-select v-model="$v.form.status.$model" :options="deliveryServiceStatusOptions">
                    Статус
                </v-select>
            </b-col>
            <b-col>
                <v-input v-model="$v.form.priority.$model" :error="errorPriority">
                    Приоритет
                </v-input>
            </b-col>
        </b-row>
        <b-row>
            <b-col>
                <v-input v-model="$v.form.pickup_priority.$model" :error="errorPickupPriority">
                    Приоритет Самовывоз
                </v-input>
            </b-col>
        </b-row>
    </b-card>
</template>

<script>
    import Services from '../../../../../../scripts/services/services.js';
    import VInput from '../../../../../components/controls/VInput/VInput.vue';
    import VDate from '../../../../../components/controls/VDate/VDate.vue';
    import VSelect from '../../../../../components/controls/VSelect/VSelect.vue';

    import {validationMixin} from 'vuelidate';
    import {integer, minValue, required} from 'vuelidate/lib/validators';

    export default {
    name: 'infopanel',
    components: {
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
            form: {
                name: this.model.name,
                registered_at: this.model.registered_at,
                status: this.model.status,
                priority: this.model.priority,
                pickup_priority: this.model.pickup_priority,
            },
        };
    },
    validations: {
        form: {
            name: {required},
            registered_at: {required},
            status: {required},
            priority: {required, integer, minValue: minValue(1)},
            pickup_priority: {required, integer, minValue: minValue(1)},
        }
    },
    methods: {
        save() {
            this.$v.$touch();
            if (this.$v.$invalid) {
                return;
            }

            Services.showLoader();
            Services.net().put(this.getRoute('deliveryService.detail.save', {id: this.deliveryService.id}), {}, this.form).then(() => {
                this.deliveryService.name = this.form.name;
                this.deliveryService.registered_at = this.form.registered_at;
                this.deliveryService.status = this.form.status;
                this.deliveryService.priority = this.form.priority;
                this.deliveryService.pickup_priority = this.form.pickup_priority;

                Services.msg("Изменения сохранены");
            }).finally(data => {
                Services.hideLoader();
            });
        },
        cancel() {
            this.form.name = this.deliveryService.name;
            this.form.registered_at = this.deliveryService.registered_at;
            this.form.status = this.deliveryService.status;
            this.form.priority = this.deliveryService.priority;
            this.form.pickup_priority = this.deliveryService.pickup_priority;
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
        errorPickupPriority() {
            if (this.$v.form.pickup_priority.$dirty) {
                if (!this.$v.form.pickup_priority.required) {
                    return "Обязательное поле!";
                }
                if (!this.$v.form.pickup_priority.integer || !this.$v.form.pickup_priority.minValue) {
                    return "Только целое число больше 0";
                }
            }
        },
    },
};
</script>
