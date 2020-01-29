<template>
    <layout-main back>
        <b-form @submit.prevent="update">
            <b-form-group
                    label="Наименование"
                    label-for="group-name"
            >
                <b-form-input
                        id="group-name"
                        v-model="productGroup.name"
                        type="text"
                        required
                        placeholder="Введите наименование"
                />
            </b-form-group>

            <b-form-group
                    label="Символьный код"
                    label-for="group-code"
            >
                <b-form-input
                        id="group-code"
                        v-model="productGroup.code"
                        type="text"
                        required
                        placeholder="Введите символьный код"
                />
            </b-form-group>

            <b-form-group
                    label="Активность"
                    label-for="group-active"
            >
                <b-form-checkbox
                        id="group-active"
                        v-model="productGroup.active"
                        :value="1"
                        :unchecked-value="0"
                        required
                />
            </b-form-group>

            <b-form-group
                    label="Наличие в меню"
                    label-for="group-added-in-menu"
            >
                <b-form-checkbox
                        id="group-added-in-menu"
                        v-model="productGroup.added_in_menu"
                        :value="1"
                        :unchecked-value="0"
                        required
                />
            </b-form-group>

            <b-form-group
                    label="Превью изображение"
                    label-for="group-preview_photo_id"
            >
                <b-form-file
                        v-model="productGroup.new_preview_photo"
                ></b-form-file>
            </b-form-group>

            <b-form-group
                    label="Тип"
                    label-for="group-type"
            >
                <b-form-select
                        v-model="productGroup.type_id"
                        id="group-type"
                >
                    <b-form-select-option
                            :value="type.id"
                            v-for="type in productGroupTypes"
                            :key="type.id"
                    >
                        {{ type.name }}
                    </b-form-select-option>
                </b-form-select>
            </b-form-group>

            <b-form-group
                    label="Категория"
                    label-for="group-categories"
            >
                <b-form-select
                        v-model="productGroup.category"
                        id="group-category"
                >
                    <b-form-select-option
                            :value="category.id"
                            v-for="category in categories"
                            :key="category.id"
                    >
                        {{ category.name }}
                    </b-form-select-option>
                </b-form-select>
            </b-form-group>

            <b-form-group
                    label="Фильтры"
                    label-for="group-filters"
            >
                <b-form-select
                        v-model="productGroup.filters"
                        id="group-type"
                >
                    <b-form-select-option
                            :value="filter.id"
                            v-for="filter in filters"
                            :key="filter.id"
                    >
                        {{ filter.name }}
                    </b-form-select-option>
                </b-form-select>
            </b-form-group>

            <b-button type="submit" variant="dark">Обновить</b-button>
        </b-form>
    </layout-main>
</template>

<script>

    import {mapGetters} from "vuex";
    import Services from "../../../../scripts/services/services";

    export default {
        components: {},
        props: {
            iProductGroup: {},
            iProductGroupTypes: {},
            iCategories: {},
            iFilters: {},
            options: {}
        },
        data() {
            return {
                productGroup: this.iProductGroup,
                productGroupTypes: this.iProductGroupTypes,
                categories: this.iCategories,
                filters: this.iFilters,
            };
        },

        methods: {
            refresh() {
                Services.net().get(this.getRoute('productGroups.detailData', {id: this.productGroup.id}))
                    .then((data) => {
                        this.productGroup = data.productGroup;
                    });
            },
            update() {

            }
        },
        computed: {
            ...mapGetters(['getRoute']),
        },
    };
</script>
<style scoped>
</style>
