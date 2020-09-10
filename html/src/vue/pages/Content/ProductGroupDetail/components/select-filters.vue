<template>
    <div class="mt-3" role="tablist">
        Фильтры<br>
        <div class="mb-2">
            <template v-for="filter in genericFilters">
                <template v-for="filterValue in filter.values">
                    <b-badge v-if="isChecked(filter.code, filterValue.code)"
                             pill
                             variant="info"
                    >
                        {{ filterValue.name }}
                    </b-badge>
                </template>
            </template>
            <span v-if="selectedFilterSources && !genericFilters.length">Фильтры для выбранных категорий не найдены</span>
        </div>

        <b-card no-body
                class="mb-1"
                v-for="filter in genericFilters"
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
            iSelectedFilterSources: Array,
            iSelectedFilters: Array,
            iProductGroupTypes: Array,
        },
        data() {
            return {
                productGroupTypes: this.iProductGroupTypes,
                selectedFilterSources: this.iSelectedFilterSources,
                selectedFilters: this.filterNormalization(this.iSelectedFilters),
                filters: {},
                genericFilters: [],
            };
        },
        methods: {
            fetchFilters(source) {
                let filterPromise = this.productGroupTypes.find(type => type.code === source)
                    ? Services.net().get(this.getRoute('productGroup.getFilters'))
                    : Services.net().get(this.getRoute('productGroup.getFiltersByCategory'), {category: source});

                Services.showLoader();
                filterPromise.then((data) => {
                    this.filters[source] = data;
                    this.mergeFilters();
                }).finally(() => {
                  Services.hideLoader();
                });
            },
            mergeFilters() {
                let allFilters = [];
                Object.values(this.filters).forEach(filtersBySource => {
                    filtersBySource.forEach(filter => {
                        allFilters.push(Object.assign({}, filter));
                    });
                });
                let groupedFilters = this.groupByCode(allFilters);
                let genericFilters = Object.values(groupedFilters)
                    .filter(arr => arr.length === Object.keys(this.filters).length)
                    .map(filterGroup => {
                        let allValues = [];
                        filterGroup.forEach(filter => {
                          allValues = allValues.concat(filter.values);
                        });
                        let groupedValues = this.groupByCode(allValues);
                        let genericValues = Object.values(groupedValues).filter(arr => arr.length === Object.keys(this.filters).length);
                        filterGroup[0]['values'] = genericValues.map(values => values[0]);
                        return filterGroup[0];
                    });
                this.genericFilters = genericFilters.filter(filter => filter.values.length > 0);
            },
            groupByCode(array) {
                let result = {};
                array.forEach(item => {
                    if (!result.hasOwnProperty(item.code)) {
                        result[item.code] = [];
                    }
                    result[item.code].push(item);
                });
                return result;
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
            iSelectedFilterSources(val) {
                let arrayDiff = val.filter(source => !this.selectedFilterSources.includes(source));
                let arrayDiffReversed = this.selectedFilterSources.filter(source => !val.includes(source));
                let addedSource = arrayDiff.length > 0 ? arrayDiff[0] : null;
                let removedSource = arrayDiffReversed.length > 0 ? arrayDiffReversed[0] : null;
                if (addedSource) {
                    this.fetchFilters(addedSource);
                }
                if (removedSource) {
                    delete this.filters[removedSource];
                    this.mergeFilters();
                }
                this.selectedFilterSources = val;
            },
            selectedFilters(val) {
                this.$emit('update', val);
            },
            genericFilters(val) {
                let groupedGenericFilters = this.groupByCode(val);
                let filteredFilters = this.selectedFilters.filter(filter => {
                    if (Object.keys(groupedGenericFilters).includes(filter.code)) {
                        let groupedFilterValues = this.groupByCode(groupedGenericFilters[filter.code][0].values);
                        return Object.keys(groupedFilterValues).includes(filter.value);
                    }
                    return false;
                });
                this.selectedFilters = filteredFilters;
            }
        },
        mounted: function () {
            this.selectedFilterSources.forEach(source => this.fetchFilters(source));
        }
    }
</script>