<template>
    <layout-main>
        <promo-code-form
            :iDiscounts="iDiscounts"
            :gifts="gifts"
            :bonuses="bonuses"
            :merchants="merchants"
            :i-segments="iSegments"
            :i-roles="iRoles"
            :i-promo-codes="iPromoCodes"
            :promo-code-types="promoCodeTypes"
            :promo-code-statuses="promoCodeStatuses"
            :submit-text="'Создать промокод'"
            :action="action"
            :processing="processing"
        ></promo-code-form>
    </layout-main>
</template>

<script>
    import PromoCodeForm from '../components/promo-code-form.vue';
    import Services from '../../../../../scripts/services/services';

    export default {
        name: 'page-promo-code-create',
        components: {
            PromoCodeForm,
            Services
        },
        props: {
            promoCodeTypes: Object,
            promoCodeStatuses: Object,
            iDiscounts: Object|Array,
            gifts: Object|Array,
            bonuses: Object|Array,
            iSegments: Array,
            iRoles: Array,
            iPromoCodes: Array,
            merchants: Array,
        },
        data() {
            return {
                processing: false,
            }
        },
        methods: {
            action(promoCode) {
                Services.showLoader();
                Services.net().get(this.getRoute('promo-code.save'), null, {
                    promoCode: promoCode
                }).then(code => {
                    this.promoCode.code = code;
                    Services.hideLoader();
                });

                Services.net().post(this.route('promo-code.save'), {}, promoCode).then(data => {
                    Services.msg("Промокод добавлен");
                }, () => {
                    Services.msg("Ошибка при добавлении промокода");
                }).finally(data => {
                    Services.hideLoader();
                    this.processing = false;
                });
            },
        },
        computed: {
        }
    }
</script>
