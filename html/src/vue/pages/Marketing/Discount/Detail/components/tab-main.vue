<template>
    <Conditions
        :discount="discount"
        :discounts="discounts"
        :conditions="discount.conditions"
        :iConditionTypes="iConditionTypes"
        :regions="discountRegions"
        :segments="segments"
        :roles="roles"
        :brands="brands"
        :categories="categories"
    ></Conditions>
</template>

<script>
    import Conditions from '../../components/conditions.vue';
    import Services from "../../../../../../scripts/services/services";

    export default {
        name: 'tab-main',
        components: {
            Conditions
        },
        props: {
            model: Object,
            discounts: Array,
            iConditionTypes: Object,
            regions: Array,
            brands: Array,
            categories: Array,
            segments: Array,
            roles: Array,
            iDistricts: Array,
        },
        data() {
            return {
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

        },
        mounted() {

        },
        created() {
            Services.event().$on('discount-condition-add', val => this.discount.conditions.push(val));
            Services.event().$on('discount-condition-delete', condType => {
                this.discount.conditions = this.discount.conditions.filter(condition => { return condition.type !== condType; });
            });
        }
    };
</script>

<style scoped>
    .condition-list {
        line-height: 0.9;
        margin-top: 10px;
    }
</style>
