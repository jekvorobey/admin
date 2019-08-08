<template>
    <layout-main>
        <h1>Заявка на съёмку</h1>
        <div class="card">
            <div class="card-body d-flex">
                <div>
                    <table>
                        <tr>
                            <td class="name">№</td>
                            <td class="value">{{ claim.id }}</td>
                        </tr>
                        <tr>
                            <td class="name">Создана</td>
                            <td class="value">{{ claim.created_at }}</td>
                        </tr>
                        <tr>
                            <td class="name">Автор</td>
                            <td class="value">{{ claim.merchant }}</td>
                        </tr>
                    </table>
                </div>
                <div class="comment">
                    <v-select v-model="$v.form.status.$model" :options="statusOptions">Статус заявки</v-select>
                    <v-input v-model="$v.form.comment.$model" tag="textarea" rows="3">Комментарий</v-input>
                </div>
            </div>
            <div class="card-footer">
                <button :disabled="!formChanged" @click="saveClaim" class="btn btn-dark">Сохранить</button>
                <button v-if="showProductsRequest" @click="createDelivery" class="btn btn-outline-dark">Создать запрос товаров</button>
            </div>
        </div>
        <h2>Товары</h2>
        <table class="table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Название</th>
                <th>Артикул</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="product in products">
                <td>{{ product.id }}</td>
                <td><a :href="getRoute('product.edit', {id: product.id})" target="_blank">{{ product.name }}</a></td>
                <td>{{ product.vendor_code }}</td>
            </tr>
            </tbody>
        </table>
    </layout-main>
</template>

<script>

    import Services from "../../../../scripts/services/services";
    import {mapGetters} from "vuex";
    import VSelect from '../../../components/controls/VSelect/VSelect.vue';
    import VInput from '../../../components/controls/VInput/VInput.vue';
    import {validationMixin} from 'vuelidate';

    export default {
        name: 'page-buffer',
        components: {
            VSelect,
            VInput,
        },
        mixins: [
            validationMixin
        ],
        props: {
            iClaim: {},
            statuses: {},
            products: Array
        },
        data() {
            return {
                claim: this.iClaim,
                form: {
                    status: this.iClaim.status,
                    comment: this.iClaim.payload.comment || '',
                }
            };
        },
        validations: {
            form: {
                status: {},
                comment: {},
            }
        },
        methods: {
            statusName(statusId) {
                return this.statuses[statusId] || 'N/A';
            },
            saveClaim() {
                Services.net()
                    .post(this.getRoute('photoClaims.update', {id: this.claim.id}), {}, this.form)
                    .then(() => {
                        this.$v.$reset();
                        this.claim.status = this.form.status;
                        this.claim.payload.comment = this.form.comment;
                    });
            },
            createDelivery() {
                Services.net()
                    .post(this.getRoute('deliveryClaims.create'), {}, {
                        productIds: this.claim.payload.productId,
                        merchantId: this.claim.payload.merchantId,
                        photoClaim: this.claim.id,
                    })
                    .then(data => {
                        this.$set(this.claim.payload, 'deliveryId', data.id);
                    });
            }
        },
        computed: {
            ...mapGetters(['getRoute']),
            statusOptions() {
                return Object.entries(this.statuses).map(entry => ({text: entry[1], value: entry[0]}))
            },
            formChanged() {
                return this.$v.$anyDirty;
            },
            showProductsRequest() {
                return parseInt(this.claim.status) === 2 && !this.claim.payload.deliveryId;
            }
        }
    };
</script>

<style scoped>
    .name {
        font-weight: bold;
        text-align: end;
    }

    .value {
        padding-left: 10px;
    }

    .comment {
        margin-left: 50px;
        flex-grow: 2;
    }
</style>
