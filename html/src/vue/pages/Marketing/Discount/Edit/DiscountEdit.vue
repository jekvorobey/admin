<template>
    <layout-main>
        <discount-form
            :i-discount="iDiscount"
            :discounts="discounts"
            :option-discount-types="optionDiscountTypes"
            :discount-statuses="discountStatuses"
            :iConditionTypes="iConditionTypes"
            :paymentMethods="paymentMethods"
            :deliveryMethods="deliveryMethods"
            :categories="categories"
            :brands="brands"
            :roles="roles"
            :iDistricts="iDistricts"
            :submit-text="'Сохранить скидку'"
            :processing="processing"
            :action="save"
        ></discount-form>

        <transition name="modal">
            <modal :close="closeModal" v-if="isModalOpen('UpdateDiscount')">
                <div slot="header">
                    <b>Обновление скидки</b>
                </div>
                <div slot="body">
                    {{ this.result }}
                </div>
            </modal>
        </transition>
    </layout-main>
</template>

<script>
    import Services from '../../../../../scripts/services/services';
    import DiscountForm from '../components/discount-form.vue';
    import modal from '../../../../components/controls/modal/modal.vue';
    import modalMixin from '../../../../mixins/modal';

    export default {
        name: 'page-discount-edit',
        components: {
            modal,
            DiscountForm
        },
        mixins: [modalMixin],
        props: {
            iDiscount: Object,
            discounts: Array,
            optionDiscountTypes: Object,
            iConditionTypes: Object,
            discountStatuses: Object,
            paymentMethods: Array,
            deliveryMethods: Array,
            categories: Array,
            brands: Array,
            roles: Array,
            iDistricts: Array,
        },
        data() {
            return {
                processing: false,
                result: '',

                // Тип условия скидки
                CONDITION_TYPE_USER: 9,
            };
        },
        methods: {
            save(discount) {
                let data = {
                    name: discount.name,
                    type: discount.type,
                    value: parseFloat(discount.value),
                    value_type: parseInt(discount.value_type),
                    start_date: discount.start_date,
                    end_date: discount.end_date,
                    conditions: discount.conditions,
                    status: discount.status,
                    promo_code_only: !!discount.promo_code_only,
                };

                switch (discount.type) {
                    case this.discountTypes.offer:
                        data.offers = this.formatIds(discount.offers);
                        break;
                    case this.discountTypes.bundle:
                        data.bundles = this.formatIds(discount.bundles);
                        break;
                    case this.discountTypes.brand:
                        data.brands = discount.brands;
                        data.except = {};
                        data.except.offers = discount.offers ? this.formatIds(discount.offers) : [];
                        break;
                    case this.discountTypes.category:
                        data.categories = discount.categories;
                        data.except = {};
                        data.except.brands = discount.brands ? discount.brands : [];
                        data.except.offers = discount.offers ? this.formatIds(discount.offers) : [];
                        break;
                }

                this.processing = true;
                let err = 'Произошла ошибка при сохранении скидки.';
                let success = 'Скидка успешно обновлена.';
                Services.showLoader();
                Services.net().put(
                    this.getRoute('discount.update', {id: discount.id}),
                    {},
                    data
                ).then(data => {
                    this.result = (data.status === 'ok') ? success : err;
                    this.openModal('UpdateDiscount');
                    this.processing = false;
                    this.setTimeout(location=this.route('discount.list'), 4000);
                }, () => {
                    this.result = err;
                    this.openModal('UpdateDiscount');
                }).finally(() => {
                    this.processing = false;
                    Services.hideLoader();
                });
            },
            formatIds(ids) {
                if (!ids) {
                    return [];
                }

                return ids
                    .split(',')
                    .map(id => { return parseInt(id); })
                    .filter(id => { return id > 0 });
            },
        },
    };
</script>
