<template>
    <div role="tablist">
        Фильтры<br>
        <div class="mb-2">
            <template v-for="filter in filters">
                <template v-for="filterValue in filter.values">
                    <b-badge v-if="isChecked(filter.code, filterValue.code)"
                             pill
                             variant="info"
                    >
                        {{ filterValue.name }}
                    </b-badge>
                </template>
            </template>
            <span v-if="selectedCategory && !filters.length">Фильтры для выбранной категории не найдены</span>
        </div>

        <b-card no-body
                class="mb-1"
                v-for="filter in filters"
                :key="filter.id"
        >
            <b-card-header header-tag="header" class="p-1" role="tab">
                <b-button block href="#" v-b-toggle="'collapse-' + filter.id" variant="info">
                    {{ filter.name ? filter.name : 'Шильдики' }}
                </b-button>
            </b-card-header>
            <b-collapse :id="'collapse-' + filter.id" accordion="my-accordion" role="tabpanel">
                <b-card-body>
                    <b-form-group>
                        <b-form-checkbox-group
                                id="checkbox-group-2"
                                v-model="selectedFilters"
                                name="flavour-2"
                        >
                            <b-form-checkbox
                                    v-for="filterValue in filter.values"
                                    :value="{
                                        code: filter.code,
                                        value: filterValue.code,
                                    }"
                                    :key="filterValue.code"
                            >
                                {{ filterValue.name }}
                            </b-form-checkbox>
                        </b-form-checkbox-group>
                    </b-form-group>
                </b-card-body>
            </b-collapse>
        </b-card>
    </div>
</template>

<script>
    import Services from '../../../../../scripts/services/services';

    export default {
        components: {},
        props: {
            iSelectedCategory: String,
            iSelectedFilters: Array,
        },
        data() {
            return {
                selectedCategory: this.iSelectedCategory,
                selectedFilters: this.filterNormalization(this.iSelectedFilters),
                filters: [],
            };
        },
        methods: {
            fetchFilters(categoryCode) {
                Services.showLoader();
                Services.net().get(this.getRoute('productGroup.getFilters'), {category: categoryCode}).then((data) => {
                    this.filters = data;
                }).finally(() => {
                    Services.hideLoader();
                });
            },
            isChecked(code, value) {
                for (let selectedFilterKey in this.selectedFilters) {
                    let selectedFilter = this.selectedFilters[selectedFilterKey];

                    if (selectedFilter.code === code && selectedFilter.value === value) {
                        return true;
                    }
                }

                return false;
            },
            filterNormalization(rawFilters) {
                if (typeof rawFilters === 'undefined') {
                    return [];
                }

                return rawFilters.map(function (filter) {
                    return {
                        code: filter.code,
                        value: filter.value,
                    };
                });
            }
        },
        watch: {
            iSelectedCategory(val) {
                this.selectedCategory = val;
            },
            selectedCategory(val) {
                this.fetchFilters(val);
            },
            selectedFilters(val) {
                this.$emit('update', val);
            }
        },
        mounted: function () {
            this.fetchFilters(this.selectedCategory);
        }
    }
</script>