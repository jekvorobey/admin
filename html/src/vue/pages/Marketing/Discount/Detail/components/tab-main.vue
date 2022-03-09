<template>
    <div class="mb-3">
        <template v-for="(condition, i) in discount.conditions">
            <condition
                ref="conditions"
                v-model="discount.conditions[i]"
                :key="i"
                class="mt-3"
                :types="iConditionTypes"
                :discount="discount"
                :discounts="discounts"
                :brands="brands"
                :roles="roles"
                :categories="categories"
                :regions="discountRegions"
                :segments="segments"
                :i-payment-methods="iPaymentMethods"
                :i-delivery-methods="iDeliveryMethods"
            />

            <div v-if="i > 0">
                <b-btn
                    variant="danger"
                    size="sm"
                    class="mb-3"
                    @click="discount.conditions.splice(i, 1)"
                >
                    Удалить условие
                </b-btn>
            </div>
        </template>

        <b-btn
            size="sm"
            :class="{
                'mt-3': discount.conditions.length > 1
            }"
            @click="addDiscountCondition"
        >Добавить дополнительное условие</b-btn>
    </div>        
</template>

<script>
    import Condition from "../../components/condition.vue";
    import Services from "../../../../../../scripts/services/services";

    export default {
        name: 'tab-main',
        components: {
            Condition
        },
        props: {
            model: Object,
            discounts: Array,
            iConditionTypes: Object,
            iDeliveryMethods: Array,
            iPaymentMethods: Array,
            regions: Array,
            brands: Array,
            categories: Array,
            segments: Array,
            roles: Array,
            iDistricts: Array,
            validateCondition: Boolean,
        },
        data() {
            return {
                newCondition: {
                    brands: "",
                    categories: [],
                    count: "",
                    deliveryMethods: [],
                    maxValue: null,
                    maxValueType: null,
                    offer: "",
                    paymentMethods: [],
                    regions: [],
                    roles: [],
                    segments: [],
                    sequenceNumber: "",
                    sum: "",
                    synergy: [],
                    type: null,
                    users: [],
                },
            }
        },
        computed: {
            discount: {
                get() {return this.model},
                set(value) {this.$emit('update:discount', value)},
            },
            discountRegions() {
                let regions = [];
                for (let i in this.iDistricts) {
                    let district = this.iDistricts[i];
                    district.regions.map(r => {
                        regions.push({text: r.name, value: r.id, group: district.name});
                    });
                }
                return regions;
            },
        },
        methods: {
            addDiscountCondition() {
                this.discount.conditions.push(this.newCondition);
            },
        },
        mounted() {

        },
        created() {
            Services.event().$on('discount-condition-add', val => this.discount.conditions.push(val));
            Services.event().$on('discount-condition-delete', condType => {
                this.discount.conditions = this.discount.conditions.filter(condition => { return condition.type !== condType; });
            });
        },

        watch: {
            validateCondition(newVal) {
                if(newVal) {
                    let isValid = true;
                    if (Array.isArray(this.$refs.conditions)) {
                        for (const conditionComponent of this.$refs.conditions) {
                            if (!conditionComponent.validate()) {
                                isValid = false;
                            }
                        }
                    }
                    this.$emit("validate", isValid);                    
                }
            },
        }
    };
</script>

<style scoped>
    .condition-list {
        line-height: 0.9;
        margin-top: 10px;
    }
</style>
