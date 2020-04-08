<template>
    <table class="table table-sm">
        <thead>
        <tr>
            <th colspan="4">
                Инфопанель
                <button class="btn btn-success btn-sm" @click="save" :disabled="!showBtn">
                    Сохранить
                </button>
                <button @click="cancel" class="btn btn-outline-danger btn-sm" :disabled="!showBtn">Отмена</button>
            </th>
        </tr>
        </thead>
        <tbody>
            <tr>
                <th>ID</th>
                <td>{{ discount.id }}</td>
            </tr>
            <tr>
                <th>Дата создания</th>
                <td>{{ discount.created_at }}</td>
            </tr>
            <tr>
                <th>Название</th>
                <td>
                    <input class="form-control form-control-sm" v-model="discount.name"/>
                </td>
            </tr>
        </tbody>
    </table>
</template>

<script>
    export default {
        name: 'discount-detail-infopanel',
        components: {

        },
        mixins: [],
        props: {
            iDiscount: Object,
        },
        data() {
            return {
                discount: {
                    name: null,
                    type: null,
                    value: null,
                    value_type: null,
                    start_date: null,
                    end_date: null,
                    offers: null,
                    bundles: null,
                    status: 1, // STATUS_ACTIVE
                    brands: [],
                    categories: [],
                    conditions: [],
                    created_at: null,
                },
                showBtn: false,
            };
        },
        methods: {
            save() {

            },
            cancel() {

            },
            initDiscount() {
                if (!this.iDiscount) {
                    return;
                }

                let discount = {...this.iDiscount};
                discount.offers = Object.values(discount.offers).map(offer => offer.offer_id).join(',');
                discount.brands = Object.values(discount.brands).map(brand => brand.brand_id);
                discount.categories = Object.values(discount.categories).map(category => category.category_id);

                let roles = discount.roles.map(role => role.role_id);
                let segments = discount.segments.map(segment => segment.segment_id);
                let conditionToObject = item => {
                    let cond = item.condition ? item.condition : {};
                    return  {
                        categories: ('categories' in cond) ? cond.categories : [],
                        deliveryMethods: ('deliveryMethods' in cond) ? cond.deliveryMethods : [],
                        paymentMethods: ('paymentMethods' in cond) ? cond.paymentMethods : [],
                        regions: ('regions' in cond) ? cond.regions : [],
                        roles: (item.type === this.CONDITION_TYPE_USER) ? roles : [],
                        segments: (item.type === this.CONDITION_TYPE_USER) ? segments : [],
                        sum: ('minPrice' in cond) ? cond.minPrice : "",
                        synergy: ('synergy' in cond) ? cond.synergy : [],
                        type: item.type,
                        users: ('customerIds' in cond) ? cond.customerIds : [],
                        count: ('count' in cond) ? cond.count : '',
                        offer: ('offer' in cond) ? cond.offer : '',
                        brands: ('brands' in cond) ? cond.brands : '',
                        sequenceNumber: ('orderSequenceNumber' in cond) ? cond.orderSequenceNumber : '',
                    }
                };

                discount.conditions = Object.values(discount.conditions).map(item => conditionToObject(item));
                if (discount.conditions.filter(item => item.type === this.CONDITION_TYPE_USER).length === 0) {
                    if (roles.length > 0 || segments.length > 0) {
                        discount.conditions.push(conditionToObject({
                            'type': this.CONDITION_TYPE_USER,
                            'condition': {
                                'roles': roles,
                                'segments': segments,
                            }
                        }));
                    }
                }

                this.discount = discount;
            },
        },
        mounted() {
            this.initDiscount();
        },
    };
</script>

