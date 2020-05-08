<template>
    <layout-main>
        <form id="form" novalidate v-on:submit.prevent.stop="send">
            <div class="row">
                <v-input v-model="bonus.name" class="col-12">Название</v-input>
            </div>

            <div class="row">
                <div class="col-3">
                    <label>Бонус на</label>
                    <select class="custom-select" v-model="bonus.type" @change="onTypeChange()">
                        <option :value="null">–</option>
                        <option v-for="type in types" :value="type.id">{{ type.name }}</option>
                    </select>
                </div>

                <div v-if="bonus.type === bonusTypes.offer" class="col-9">
                    <v-input v-model="offerIds"
                             :help="'ID офферов через запятую'"
                    >Офферы</v-input>
                </div>

                <template v-if="bonus.type === bonusTypes.brand">
                    <BrandsSearch
                            key="brands-search-main"
                            classes="col-9"
                            title="Бренды"
                            :brands="brands"
                            :i-brands="bonus.brands"
                            @update="updateBrands"
                    ></BrandsSearch>

                    <div class="offset-3 col-9">
                        <v-input v-model="offerIds" :help="'ID офферов через запятую'">За исключением офферов</v-input>
                    </div>
                </template>

                <template v-if="bonus.type === bonusTypes.category">
                    <CategoriesSearch
                            classes="col-9"
                            title="Категории"
                            :categories="categories"
                            :i-categories="bonus.categories"
                            @update="updateCategories"
                    ></CategoriesSearch>

                    <BrandsSearch
                            key="brands-search-except"
                            classes="col-9 offset-3"
                            title="За исключением брендов"
                            :brands="brands"
                            @update="updateBrands"
                    ></BrandsSearch>

                    <div class="offset-3 col-9">
                        <v-input v-model="offerIds" :help="'ID офферов через запятую'">За исключением офферов</v-input>
                    </div>
                </template>
            </div>

            <div class="row mt-3">
                <div class="col-3">
                    <label>Тип значения</label>
                    <select class="custom-select" v-model="bonus.value_type">
                        <option v-for="valueType in valueTypes" :value="valueType.id">{{ valueType.name }}</option>
                    </select>
                </div>

                <v-input v-model="bonus.value" class="col-3" type="number" min="0">Значение в {{ valueTypeName }}</v-input>
            </div>

            <div class="row mt-3">
                <div class="col-12">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="promoCodeOnlyBtn" key="promoCodeOnlyBtn" v-model="bonus.promo_code_only">
                        <label class="custom-control-label" for="promoCodeOnlyBtn">Бонусы доступны только по промокоду</label>
                    </div>
                </div>

                <div class="col-12 mt-3">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="validPeriodBtn" key="validPeriodBtn" v-model="validPeriodBtn">
                        <label class="custom-control-label" for="validPeriodBtn">Неограниченный срок действия</label>
                    </div>
                </div>

                <v-input v-model="bonus.valid_period"
                         v-if="!validPeriodBtn"
                         class="col-3 mt-3"
                         type="number"
                         min="0"
                >Срок действия бонусов (в днях)</v-input>
            </div>

            <div class="row">
                <div class="col-3 mb-3 mt-3">
                    <label for="start_date">Дата старта</label>
                    <b-form-input id="start_date" v-model="bonus.start_date" type="date"></b-form-input>
                </div>
                <div class="col-3 mt-3">
                    <label for="end_date">Дата окончания</label>
                    <b-form-input id="end_date" v-model="bonus.end_date" type="date"></b-form-input>
                </div>
            </div>

            <div class="row">
                <div class="col-3">
                    <label>Статус</label>
                    <select class="custom-select" v-model="bonus.status">
                        <option :value="null">–</option>
                        <option v-for="status in statuses" :value="status.id">{{ status.name }}</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-12 mt-5">
                    <button type="submit" class="btn btn-success">Создать правило</button>
                </div>
            </div>
        </form>
    </layout-main>
</template>

<script>
    import VInput from '../../../../components/controls/VInput/VInput.vue';
    import Services from "../../../../../scripts/services/services";
    import BrandsSearch from '../../Discount/components/brands-search.vue';
    import CategoriesSearch from '../../Discount/components/categories-search.vue';

    export default {
        components: {
            VInput,
            Services,
            BrandsSearch,
            CategoriesSearch,
        },
        props: {
            types: Object,
            statuses: Object,
            brands: Array,
            categories: Array,
        },
        data() {
            return {
                bonus: {
                    name: null,
                    status: null,
                    type: null,
                    value: null,
                    value_type: 1,
                    value_period: null,
                    start_date: null,
                    end_date: null,
                    promo_code_only: null,
                    offers: [],
                    brands: [],
                    services: [],
                    categories: [],
                },
                offerIds: '',
                validPeriodBtn: true
            }
        },
        methods: {
            send() {
                Services.showLoader();
                Services.net().post(this.getRoute('bonus.save'), {}, this.bonus).then(() => {
                   // location.reload();
                }).finally(() => {
                    Services.hideLoader();
                })
            },
            onTypeChange() {
                this.offerIds = '';
                this.bonus.offers = [];
                this.bonus.brands = [];
                this.bonus.categories = [];
            },
            updateBrands(brands) {
                this.bonus = {...this.bonus, brands};
            },
            updateCategories(categories) {
                this.bonus = {...this.bonus, categories};
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
        computed: {
            valueTypes() {
                return [
                    {name: 'Проценты', id: this.bonusValueTypes.percent},
                    {name: 'Абсолютное значение', id: this.bonusValueTypes.absolute}
                ];
            },
            valueTypeName() {
                return this.bonus.value_type === this.bonusValueTypes.percent ? 'процентах' : 'бонусах';
            }
        },
        watch: {
            validPeriodBtn() {
                this.bonus.valid_period = null;
            },
            offerIds(val, oldVal) {
                if (val && val !== oldVal) {
                    let format = this.formatIds(this.offerIds).join(', ');
                    let separator = val.slice(-1) === ','
                        ? ','
                        : (val.slice(-2) === ', ' ? ', ' : '');
                    this.offerIds = format + separator;
                }

                this.bonus.offers = this.formatIds(this.offerIds).filter((v, i, a) => a.indexOf(v) === i);
            }
        }
    }
</script>
