<template>
    <div>
        <div class="row">
            <div class="col-sm-7">
                <div class="card p-4">
                    <div class="card-body">
                        <h5 class="card-title">Подписки пользователя</h5>

                        <div v-for="(topic, index) in topics" class="custom-control custom-checkbox">
                            <input type="checkbox"
                                   v-model="customer.topics"
                                   :value="topic.id"
                                   :id="'topic'+index"
                                   class="custom-control-input"
                                   :disabled="customer.periodicity === 0">
                            <label class="custom-control-label" :for="'topic'+index">
                                {{topic.name}}
                            </label>
                        </div>

                        <button @click="save"
                                class="btn btn-md btn-success mt-3"
                                :title="customer.periodicity === null ? 'Не указана периодичность уведомлений':''"
                                :disabled="customer.periodicity === null">
                            Сохранить изменения
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-sm-5 mb-2">
                <div class="card p-3 mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Периодичность уведомлений</h5>
                        <div v-for="(period, index) in periods" class="custom-control custom-radio">
                            <input type="radio"
                                   v-model="customer.periodicity"
                                   :value="index"
                                   :id="'period'+index"
                                   class="custom-control-input"
                                   checked>
                            <label class="custom-control-label" :for="'period'+index">
                                {{period}}
                            </label>
                        </div>
                    </div>
                </div>

                    <div class="card p-3">
                        <div class="card-body">
                            <h5 class="card-title">Предпочитаемый способ связи</h5>
                            <div v-for="(channel, index) in channels" class="custom-control custom-checkbox">
                                <input type="checkbox"
                                       v-model="customer.channels"
                                       :value="index"
                                       :id="'channel'+index"
                                       class="custom-control-input"
                                       :disabled="customer.periodicity === 0">
                                <label class="custom-control-label" :for="'channel'+index">
                                    {{channel}}
                                </label>
                            </div>
                        </div>
                    </div>
            </div>
        </div>
    </div>
</template>

<script>
    import Services from "../../../../../scripts/services/services.js";

    export default {
        name: "tab-subscriptions",
        props: ['model'],
        data() {
            return {
                topics: null,
                periods: null,
                channels: null,
                customer: {
                    topics: null,
                    periodicity: null,
                    channels: null,
                }
            }
        },
        methods: {
            refresh() {
                Services.showLoader();
                Services.net().get(this.getRoute('customers.detail.newsletter',
                    {id: this.model.id}))
                    .then(data => {
                        this.topics = data.topics;
                        this.periods = data.periods;
                        this.channels = data.channels;
                        this.customer.topics = JSON.parse(data.customer.topics) || [];
                        this.customer.periodicity = data.customer.periodicity;
                        this.customer.channels = JSON.parse(data.customer.channels) || [];
                    }).finally(() => {
                    Services.hideLoader();
                })
            },
            save() {
                Services.showLoader();
                Services.net().put(this.getRoute('customers.detail.newsletter.edit',
                    {id: this.model.id}), this.customer)
                    .then(() => {
                        this.refresh();
                        Services.msg('Изменения сохранены')
                    }, () => {
                        Services.msg('Не удалось сохранить параметры подписки','danger')
                    }).finally(() => {
                    Services.hideLoader();
                })
            }
        },
        created() {
            this.refresh();
        }
    }
</script>

<style scoped>

</style>