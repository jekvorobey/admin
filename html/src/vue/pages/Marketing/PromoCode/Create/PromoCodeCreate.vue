<template>
    <layout-main>
        <promo-code-form
            :iDiscounts="iDiscounts"
            :gifts="gifts"
            :bonuses="bonuses"
            :merchants="merchants"
            :i-types-for-merchant="iTypesForMerchant"
            :i-segments="iSegments"
            :i-roles="iRoles"
            :i-promo-codes="iPromoCodes"
            :i-types="iTypes"
            :i-statuses="iStatuses"
            :i-types-of-limit="iTypesOfLimit"
            :submit-text="'Сохранить'"
            :action="action"
            :processing="processing"
            :referral="referral"
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
            iTypes: Object,
            iStatuses: Object,
            iDiscounts: Object|Array,
            gifts: Object|Array,
            bonuses: Object|Array,
            iSegments: Array,
            iRoles: Array,
            iPromoCodes: Array,
            merchants: Array,
            iTypesForMerchant: Array,
            iTypesOfLimit: Object,
            returnUrl: {},
            referral: {}
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
                    window.location.href = this.returnUrl;
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
