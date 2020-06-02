<template>
    <div class="d-flex flex-wrap align-items-stretch justify-content-start product-header">
        <div class="flex-grow-1 d-flex justify-content-center">
            <div class="d-flex flex-column" style="width: 75%">
                <div class="card border-0 shadow w-100">
                    <div class="card-header">
                        Комментарий мерчанта
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <textarea class="form-control" rows="3" v-model="claim.merchant_message" placeholder="Нет комментариев" readonly></textarea>
                        </div>
                    </div>
                </div>
                <div class="card border-0 shadow w-100 mt-4">
                    <div class="card-header">
                        Служебный комментарий
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <textarea class="form-control" :class="{ 'is-invalid': errorServiceComment() }" rows="3" v-model="$v.service_message.$model" placeholder="Введите комментарий"></textarea>
                            <span class="invalid-feedback" role="alert">
                            {{ errorServiceComment() }}
                        </span>
                        </div>
                        <button @click="saveMessage()"
                                :disabled="!$v.service_message.$dirty"
                                class="btn btn-sm btn-dark">
                            Сохранить
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex-grow-1 d-flex justify-content-center justify-content-lg-start mt-4 mt-lg-0" style="height: 80%">
            <div class="shadow p-3" style="width: 80%">
                <h2>Заявка на производство контента #{{ claim.id }}</h2>
                <div style="height: 40px; margin-bottom: 5%;">
                    <span class="badge" :class="statusClass(claim.status)">{{ this.statusName(claim.status) }}</span>
                </div>
                <p class="text-secondary mb-1">ID: <span class="float-right">{{ claim.id }}</span></p>
                <p class="text-secondary mb-1">Мерчант: <span class="float-right">{{ claim.merchantName }}</span></p>
                <p class="text-secondary mb-1">Тип заявки: <span class="float-right">{{ this.typeName(claim.type) }}</span></p>
                <p class="text-secondary mb-1">Тип фотосъемки: <span class="float-right">{{ this.unpackName(claim.unpacking) }}</span></p>
                <p class="text-secondary mb-1">Автор: <span class="float-right">{{ claim.userName }}</span></p>
                <p class="text-secondary mb-1">Дата создания: <span class="float-right">{{ claim.created_at }}</span></p>
            </div>
        </div>
    </div>
</template>

<script>
    import { validationMixin } from 'vuelidate';
    import { required } from 'vuelidate/lib/validators';
    import Services from "../../../../../scripts/services/services";

    export default {
        props: {
            claim: {},
            options: {}
        },
        data () {
            return {
                service_message: this.claim.service_message,
            }
        },
        mixins: [validationMixin],
        validations() {
            return {
                service_message: {
                    required
                }
            }
        },
        methods: {
            statusName(statusId) {
                return this.options.statuses[statusId] || 'N/A';
            },
            typeName(typeId) {
                return this.options.types[typeId] || 'N/A';
            },
            unpackName(unpackId) {
                return this.options.unpack[unpackId] || '—';
            },
            errorServiceComment() {
                if (this.$v.service_message.$dirty) {
                    if (!this.$v.service_message.required) return "Поле комментария должно быть заполнено!";
                }
            },
            saveMessage() {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }
                let data = {
                    'service_message': this.$v.service_message.$model
                };

                Services.net().put(this.getRoute('contentClaims.update', {id: this.claim.id }), {}, data, {}, true)
                    .then(result => {
                        this.$emit('onMessageSave', result);
                    }, () => {
                        Services.msg("Не удалось сохранить комментарий", 'danger');
                    });
            },
            statusClass(statusId) {
                switch (statusId) {
                    case 1: return 'badge-info';
                    case 2: return 'badge-primary';
                    case 3: return 'badge-success';
                    case 4: return 'badge-warning';
                    case 5: return 'badge-light';
                    case 6: return 'badge-secondary';
                    case 7: return 'badge-danger';
                }
            },
        }
    }
</script>