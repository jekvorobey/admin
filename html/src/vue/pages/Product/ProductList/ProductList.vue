<template>
    <layout-main>
        <div class="card">
            <div class="card-header">
                Фильтр
                <button @click="toggleHiddenFilter" class="btn btn-sm btn-light float-right">
                    {{ opened ? 'Меньше' : 'Больше' }} фильтров
                    <fa-icon :icon="opened ? 'compress-arrows-alt' : 'expand-arrows-alt'"></fa-icon>
                </button>
            </div>
            <div class="card-body">
                <div class="row">
                    <f-input v-model="filter.name" class="col-md-4 col-sm-12">Название</f-input>
                    <f-input v-model="filter.vendorCode" class="col-md-2 col-sm-12">Артикул</f-input>
                    <f-input v-model="filter.id" class="col-md-2 col-sm-12">ID</f-input>
                    <f-select
                            v-model="filter.active"
                            :options="activeOptions"
                            class="col-md-2 col-sm-12"
                    >На витрине</f-select>
                    <f-select
                            v-model="filter.archive"
                            :options="archiveOptions"
                            class="col-md-2 col-sm-12"
                    >Архивность</f-select>
                </div>

                <transition name="slide">
                    <div v-if="opened">
                        <div class="additional-filter pt-3 mt-3">
                            <div class="row">
                                <f-input v-model="filter.priceFrom" class="col-lg-2 col-md-3 col-sm-12">
                                    Цена
                                    <template #prepend><span class="input-group-text">от</span></template>
                                    <template #append><span class="input-group-text">руб.</span></template>
                                </f-input>
                                <f-input v-model="filter.priceTo" class="col-lg-2 col-md-3 col-sm-12">
                                    &nbsp;
                                    <template #prepend><span class="input-group-text">до</span></template>
                                    <template #append><span class="input-group-text">руб.</span></template>
                                </f-input>

                                <f-input v-model="filter.qtyFrom" class="col-lg-2 col-md-3 col-sm-12">
                                    Кол-во
                                    <template #prepend><span class="input-group-text">от</span></template>
                                </f-input>
                                <f-input v-model="filter.qtyTo" class="col-lg-2 col-md-3 col-sm-12">
                                    &nbsp;
                                    <template #prepend><span class="input-group-text">до</span></template>
                                </f-input>

                                <f-date
                                        v-model="filter.dateFrom"
                                        class="col-lg-2 col-md-3 col-sm-12"
                                >Дата содания от</f-date>
                                <f-date
                                        v-model="filter.dateTo"
                                        class="col-lg-2 col-md-3 col-sm-12"
                                >Дата содания до</f-date>
                            </div>
                            <div class="row">
                                <f-select
                                    v-model="filter.isPriceHidden"
                                    :options="priceHiddenOptions"
                                    class="col-md-3 col-sm-12"
                                >Видимость цен</f-select>
                            </div>
                            <div class="row">
                                <f-select
                                        v-model="filter.brand"
                                        :options="brandOptions"
                                        class="col-md-3 col-sm-12"
                                >Бренд</f-select>
                                <f-select
                                        v-model="filter.category"
                                        :options="categoryOptions"
                                        class="col-md-3 col-sm-12">
                                    Категория
                                    <template #help>Будут показаны товары выбранной и всех дочерних категорий</template>
                                </f-select>
                                <f-select
                                        v-model="filter.approvalStatus"
                                        :options="approvalOptions"
                                        class="col-md-3 col-sm-12"
                                >Согласовано</f-select>
                                <f-select
                                        v-model="filter.productionStatus"
                                        :options="productionOptions"
                                        class="col-md-3 col-sm-12"
                                >Контент</f-select>
                            </div>
                            <div class="row">
                                <f-select
                                    v-model="filter.merchant"
                                    :options="merchantOptions"
                                    class="col-md-3 col-sm-12"
                                >Мерчант</f-select>
                                <f-select
                                    v-model="filter.badges"
                                    :options="badgeOptions"
                                    class="col-md-3 col-sm-12"
                                >Шильдики</f-select>
                            </div>
                        </div>
                    </div>
                </transition>
            </div>
            <div class="card-footer">
                <button @click="applyFilter" class="btn btn-sm btn-dark">Применить</button>
                <button @click="clearFilter" class="btn btn-sm btn-outline-dark">Очистить</button>
                <span class="float-right">Всего товаров: {{ total }}.</span>
            </div>
        </div>

        <div class="d-flex justify-content-between mt-3 mb-3">
            <div class="action-bar d-flex justify-content-start">
                <span class="mr-4">Выбрано товаров: {{massAll(massProductsType).length}}</span>
                <b-button-group class="mr-4">
                    <b-button variant="success"
                              v-if="massEmpty(massProductsType)"
                              @click="exportProductsByFilters"
                    >Экспорт отфильтрованных</b-button>
                    <b-dropdown right
                                split
                                variant="success"
                                text="Экспорт отфильтрованных"
                                v-if="!massEmpty(massProductsType)"
                                @click="exportProductsByFilters"
                    >
                        <b-dropdown-item @click="exportProductsById(massAll(massProductsType))">Экспорт выбранных</b-dropdown-item>
                    </b-dropdown>
                </b-button-group>
                <dropdown v-if="!massEmpty(massProductsType)" :items="statusItems" @select="startChangeStatus" class="mr-4 order-btn">
                    Сменить статус
                </dropdown>
                <button v-if="!massEmpty(massProductsType)"
                        @click="copyOfferIdsToClipBoard"
                        type="button"
                        class="btn btn-outline-secondary mr-3">
                    <fa-icon icon="copy"></fa-icon> Копировать ID офферов</button>
                <button v-if="!massEmpty(massProductsType)"
                        @click="copyProductIdsToClipBoard"
                        type="button"
                        class="btn btn-outline-secondary mr-3">
                    <fa-icon icon="copy"></fa-icon> Копировать ID товаров</button>
                <button v-if="!massEmpty(massProductsType)"
                        @click="copyArticlesToClipBoard"
                        type="button"
                        class="btn btn-outline-secondary mr-3">
                    <fa-icon icon="copy"></fa-icon> Копировать артикулы</button>

                <button v-if="!massEmpty(massProductsType)"
                        @click="openBadgesEditModal"
                        type="button"
                        class="btn btn-success mr-3">
                    <fa-icon icon="cog"></fa-icon> Назначить шильдики</button>
            </div>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>
                        <input type="checkbox"
                               :checked="checkAll"
                               @change="e => massCheckboxAllOnPage(e)"/>
                    </th>
                    <th>ID</th>
                    <th></th>
                    <th class="with-small">Название <small>Артикул</small></th>
                    <th>Бренд</th>
                    <th>Категория</th>
                    <th>Дата создания</th>
                    <th>Стоимость</th>
                    <th>Количество</th>
                    <th>На витрине</th>
                    <th>В архиве</th>
                    <th>Контент</th>
                    <th>Согласование</th>
                    <th>Отгружен в Shoppilot</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="product in products">
                    <td class="d-flex flex-column align-items-center">
                        <input type="checkbox"
                                :checked="massHas({type:massProductsType, id:product.id})"
                                @change="e => massCheckbox(e, massProductsType, product.id)"/>
                        <fa-icon
                                v-if="!product.ready"
                                icon="exclamation-triangle"
                                class="text-warning mt-2"
                                title="Товар помечен для индексирования"/>
                    </td>
                    <td>{{product.id}}</td>
                    <td><img :src="imageUrl(product.catalogImageId, 100, 100)" class="preview"></td>
                    <td class="with-small">
                        <a :href="getRoute('products.detail', {id: product.id})">{{product.name}}</a>
                        <small>{{product.vendorCode}}</small>
                    </td>
                    <td>{{product.brandName}}</td>
                    <td>{{product.categoryName}}</td>
                    <td>{{formatDate(product.dateAdd)}}</td>
                    <td>{{product.price ? product.price : '--'}}</td>
                    <td>{{product.qty ? roundValue(product.qty) : '--'}}</td>
                    <td>
                        <span class="badge" :class="{'badge-success':product.active,'badge-danger':!product.active}">
                            {{product.active ? 'Да' : 'Нет'}}
                        </span>
                        <template v-if="!product.active">
                            <fa-icon icon="question-circle" :id="'product-strikes-popover' + product.id"></fa-icon>
                            <b-popover :target="'product-strikes-popover' + product.id" triggers="hover">
                                <ul class="list-group ml-3">
                                    <li v-for="strike in product.strikes">{{ strike }}</li>
                                </ul>
                            </b-popover>
                        </template>
                    </td>
                    <td>
                        <span class="badge" :class="{'badge-success':!product.archive,'badge-danger':product.archive}">
                            {{product.archive ? 'Да' : 'Нет'}}
                        </span>
                    </td>
                    <td>
                        <span class="badge" :class="{'badge-success':isProductionDone(product.productionStatus),'badge-danger':!isProductionDone(product.productionStatus)}">
                            {{productionName(product.productionStatus)}}
                        </span>
                    </td>
                    <td>
                        <span class="badge" :class="{'badge-success':isApprovalDone(product.approvalStatus),'badge-danger':!isApprovalDone(product.approvalStatus)}">
                            {{approvalName(product.approvalStatus)}}
                        </span>
                    </td>
                    <td>
                        <span v-if="'shoppilotExist' in product" class="badge" :class="{'badge-success': product.shoppilotExist,'badge-danger':!product.shoppilotExist}">
                            {{(product.shoppilotExist) ? 'Да' : 'Нет'}}
                        </span>
                        <template v-else>Информация временно недоступна</template>
                    </td>
                </tr>
            </tbody>
        </table>
        <div>
            <b-pagination
                    v-if="numPages !== 1"
                    v-model="page"
                    :total-rows="total"
                    :per-page="pageSize"
                    :hide-goto-end-buttons="numPages < 10"
                    class="mt-3 float-right"
            ></b-pagination>
        </div>
        <transition name="modal">
            <modal :close="closeModal" v-if="isModalOpen('StatusCommentModal')">
                <div slot="header">
                    Комментарий
                </div>
                <div slot="body">
                    <v-input tag="textarea" v-model="$v.statusComment.$model">
                        Комментарий
                    </v-input>
                    <button @click="applyStatus" class="btn btn-dark mt-3" :disabled="!$v.$anyDirty">Сохранить</button>
                </div>
            </modal>
        </transition>

        <product-badges-modal
            :product-id="massAll(this.massProductsType)"
            :available-badges="options.availableBadges"
            @save="() => { massClear(massProductsType); loadPage(iCurrentPage) }"
        />
    </layout-main>
</template>

<script>
    import withQuery from 'with-query';

    import { BButtonGroup, BButton, BDropdown, BDropdownItem, BDropdownDivider } from 'bootstrap-vue';
    import FSelect from '../../../components/filter/f-select.vue';
    import FInput from '../../../components/filter/f-input.vue';
    import FDate from '../../../components/filter/f-date.vue';
    import VInput from '../../../components/controls/VInput/VInput.vue';
    import Dropdown from '../../../components/dropdown/dropdown.vue';
    import Modal from '../../../components/controls/modal/modal.vue';
    import ProductBadgesModal from '../ProductDetail/components/product-badges-modal.vue';
    import { mapActions, mapGetters } from 'vuex';

    import {
        ACT_LOAD_PAGE,
        ACT_UPDATE_APPROVAL,
        ACT_UPDATE_ARCHIVE,
        ACT_UPDATE_PRODUCTION,
        GET_LIST,
        GET_NUM_PAGES,
        GET_PAGE_NUMBER,
        GET_PAGE_SIZE,
        GET_TOTAL,
        NAMESPACE,
        SET_PAGE,
    } from '../../../store/modules/products';

    import modalMixin from '../../../mixins/modal';
    import mediaMixin from '../../../mixins/media';
    import massSelectionMixin from '../../../mixins/mass-selection';
    import { validationMixin } from 'vuelidate';
    import { required } from 'vuelidate/lib/validators';
    import Helpers from '../../../../scripts/helpers';
    import * as clipboard from "clipboard-polyfill";
    import Services from "../../../../scripts/services/services";

    const TYPE_ARCHIVE = 'archive';
    const TYPE_PRODUCTION = 'production';
    const TYPE_APPROVAL = 'approval';

    const cleanHiddenFilter = {
        brand: '',
        category: '',
        merchant: '',
        badges: '',
        approvalStatus: '',
        productionStatus: '',
        priceFrom: null,
        priceTo: null,
        qtyFrom: null,
        qtyTo: null,
        dateFrom: null,
        dateTo: null,
        isPriceHidden: null,
    };

    const cleanFilter = Object.assign({
        id: '',
        name: '',
        vendorCode: '',
        active: null,
        archive: null,
    }, cleanHiddenFilter);

    export default {
        mixins: [
            modalMixin,
            mediaMixin,
            massSelectionMixin,
            validationMixin
        ],
        components: {
            BButtonGroup,
            BButton,
            BDropdown,
            BDropdownItem,
            BDropdownDivider,
            Modal,
            ProductBadgesModal,
            FSelect,
            FInput,
            FDate,
            Dropdown,
            VInput,
        },
        props: {
            iProducts: {},
            iTotal: {},
            iCurrentPage: {},
            iFilter: {},
            options: {}
        },
        data() {
            this.$store.commit(`${NAMESPACE}/${SET_PAGE}`, {
                list: this.iProducts,
                total: this.iTotal,
                page: this.iCurrentPage
            });
            let filter = Object.assign({}, JSON.parse(JSON.stringify(cleanFilter)), this.iFilter);
            let statusDropdown = [
                {value: {type: TYPE_ARCHIVE, value: true}, text: 'В архив'},
                {value: {type: TYPE_ARCHIVE, value: false}, text: 'Из архива'},
            ];

            for (let id in this.options.productionStatuses) {
                let status = this.options.productionStatuses[id];
                statusDropdown.push({
                    value: {type: TYPE_PRODUCTION, value: status.id},
                    text: `Контент: ${status.name}`,
                });
            }

            for (let id in this.options.approvalStatuses) {
                let status = this.options.approvalStatuses[id];
                statusDropdown.push({
                    value: {type: TYPE_APPROVAL, value: status.id},
                    text: `Согласование: ${status.name}`,
                });
            }

            return {
                filter,
                opened: false,
                statusItems: statusDropdown,

                statusComment: '',
                massActionType: null,
                massStatus: null,

                massProductsType: 'products',
            };
        },
        validations: {
            statusComment: {required},
        },
        methods: {
            roundValue(value) {
                return Helpers.roundValue(value)
            },
            ...mapActions(NAMESPACE, [
                ACT_LOAD_PAGE,
                ACT_UPDATE_PRODUCTION,
                ACT_UPDATE_ARCHIVE,
                ACT_UPDATE_APPROVAL
            ]),
            loadPage(page) {
                let cleanFilter = {};
                for (let [key, value] of Object.entries(this.filter)) {
                    if (value !== undefined && value !== null && value !== '') {
                        cleanFilter[key] = value;
                    }
                }
                history.pushState(null, null, location.origin + location.pathname + withQuery('', {
                    page: page,
                    filter: cleanFilter,
                }));

                return this[ACT_LOAD_PAGE]({
                    page: page,
                    filter: cleanFilter
                });
            },

            applyFilter() {
                this.loadPage(1);
                this.massClear(this.massProductsType);
            },
            clearFilter() {
                this.$set(this, 'filter', JSON.parse(JSON.stringify(cleanFilter)));
                this.applyFilter();
                this.massClear(this.massProductsType);
            },
            toggleHiddenFilter() {
                this.opened = !this.opened;
                if (this.opened === false) {
                    for (let entry of Object.entries(cleanHiddenFilter)) {
                        this.filter[entry[0]] = JSON.parse(JSON.stringify(entry[1]));
                    }
                    this.applyFilter();
                }
            },
            isHiddenFilterDefaultOpen() {
                for (let entry of Object.entries(cleanHiddenFilter)) {
                    if (!Helpers.isEqual(entry[1], this.filter[entry[0]]) && entry[1] !== this.filter[entry[0]]) {
                        return true;
                    }
                }
                return false;
            },

            approvalName(status) {
                if (this.options.approvalStatuses[status]) {
                    return this.options.approvalStatuses[status].name;
                }
                return 'N/A';
            },
            productionName(status) {
                if (this.options.productionStatuses[status]) {
                    return this.options.productionStatuses[status].name;
                }
                return 'N/A';
            },
            isApprovalDone(status) {
                return status === this.options.approvalDone;
            },
            isProductionDone(status){
                return status === this.options.productionDone;
            },
            formatDate(date) {
                return new Date(date).toLocaleDateString();
            },
            startChangeStatus(action) {
                this.massActionType = action.type;
                this.massStatus = action.value;
                this.statusComment = '';
                switch (action.type) {
                    case TYPE_ARCHIVE:
                        if (action.value) {
                            this.openModal('StatusCommentModal');
                            return;
                        }
                        break;
                    case TYPE_PRODUCTION:
                        if (action.value === this.options.productionCancel) {
                            this.openModal('StatusCommentModal');
                            return;
                        }
                        break;
                    case TYPE_APPROVAL:
                        if (action.value === this.options.approvalCancel) {
                            this.openModal('StatusCommentModal');
                            return;
                        }
                        break;
                }
                this.applyStatus();
            },
            applyStatus() {
                this.closeModal();
                let ids = this.massAll(this.massProductsType);
                let promise;
                switch (this.massActionType) {
                    case TYPE_ARCHIVE:
                        promise = this[ACT_UPDATE_ARCHIVE]({ids, status: this.massStatus, comment: this.statusComment});
                        break;
                    case TYPE_PRODUCTION:
                        promise = this[ACT_UPDATE_PRODUCTION]({ids, status: this.massStatus, comment: this.statusComment});
                        break;
                    case TYPE_APPROVAL:
                        promise = this[ACT_UPDATE_APPROVAL]({ids, status: this.massStatus, comment: this.statusComment});
                        break;
                }
                promise.then(() => {
                    this.loadPage(this.page).then(() => {
                        this.massClear(this.massProductsType);
                    });
                });
            },
            copyOfferIdsToClipBoard() {
                let text = this.massAll(this.massProductsType)
                    .map(x => this.iProducts.find(prod => prod.id === x).offerId).join(',');
                clipboard.writeText(text).then();
            },
            copyProductIdsToClipBoard() {
                let text = this.massAll(this.massProductsType).join(',');
                clipboard.writeText(text).then();
            },
            copyArticlesToClipBoard() {
                let text = this.massAll(this.massProductsType)
                    .map(x => this.iProducts.find(prod => prod.id === x).vendorCode).join(',');
                clipboard.writeText(text).then();
            },
            openBadgesEditModal() {
                this.$bvModal.show('productBadgesEdit');
            },
            exportProductsById(productIds) {
                Services.showLoader();
                Services.net().get(
                    this.route('products.exportByProductIds'),
                    {'product_ids': productIds}
                ).then(data => {
                        let link = document.createElement("a");
                        link.href = data.path;
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                        link = null;

                        Services.msg("Товары экспортированы");
                    },
                    () => {
                        Services.msg("Ошибка экспорта товаров", 'danger');
                    }
                ).finally(() => {
                    Services.hideLoader();
                });
            },
            exportProductsByFilters() {
                Services.showLoader();
                Services.net().get(
                    this.route('products.exportByFilters'),
                    {'filters': this.filter}
                ).then(data => {
                        let link = document.createElement("a");
                        link.href = data.path;
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                        link = null;

                        Services.msg("Товары экспортированы");
                    },
                    () => {
                        Services.msg("Ошибка экспорта товаров", 'danger');
                    }
                ).finally(() => {
                    Services.hideLoader();
                });
            },
            massCheckboxAllOnPage(e) {
                if (e.target.checked) {
                    this.products.forEach(product => {
                        if (!this.massAll(this.massProductsType).includes(product.id)) {
                            this.massCheckbox(e, this.massProductsType, product.id);
                        }
                    });
                } else {
                    this.products.forEach(product => {
                        if (this.massAll(this.massProductsType).includes(product.id)) {
                            this.massCheckbox(e, this.massProductsType, product.id);
                        }
                    });
                }
            },
        },
        created() {
            window.onpopstate = () => {
                let query = qs.parse(document.location.search.substr(1));
                if (query.page) {
                    this.page = query.page;
                }
            };
            this.opened = this.isHiddenFilterDefaultOpen();
        },
        computed: {
            ...mapGetters(NAMESPACE, {
                GET_PAGE_NUMBER,
                total: GET_TOTAL,
                pageSize: GET_PAGE_SIZE,
                numPages: GET_NUM_PAGES,
                products: GET_LIST,
            }),
            page: {
                get: function () {
                    return this.GET_PAGE_NUMBER;
                },
                set: function (page) {
                    this.loadPage(page);
                }
            },
            brandOptions() {
                return this.options.brands.map(brand => ({value: brand.code, text: brand.name}));
            },
            merchantOptions() {
                return this.options.merchants.map(merchant => ({value: merchant.id, text: merchant.legal_name}));
            },
            badgeOptions() {
                return this.options.availableBadges.map(badge => ({value: badge.code, text: badge.text}));
            },
            categoryOptions() {
                const groups = this.options.categories.filter(category => !category.parent_id);
                const groupNames = new Map();
                for (let group of groups) {
                    groupNames.set(group.id, group.name);
                }
                return this.options.categories
                    .filter(category => category.parent_id)
                    .map(category => ({
                        value: category.code,
                        text: category.name,
                        group: groupNames.get(category.parent_id)
                    }));

            },
            approvalOptions() {
                return Object.values(this.options.approvalStatuses).map(({id, name}) => ({value: id, text: name}));
            },
            productionOptions() {
                return Object.values(this.options.productionStatuses).map(({id, name}) => ({value: id, text: name}));
            },
            archiveOptions() {
                return [
                    {value: true, text: 'В архиве'},
                    {value: false, text: 'Не в архиве'},
                ];
            },
            activeOptions() {
                return [
                    {value: true, text: 'Да'},
                    {value: false, text: 'Нет'},
                ];
            },
            priceHiddenOptions() {
                return [
                  {value: false, text: 'Цены видны'},
                  {value: true, text: 'Цены спрятаны'},
                ];
            },
            checkAll() {
                let BreakException = {};

                try {
                    this.products.forEach(product => {
                        if (!this.massAll(this.massProductsType).includes(product.id))
                            throw BreakException;
                    });
                } catch (e) {
                    if (e !== BreakException) throw e;
                    return false;
                }
                return true;
            },
        }
    };
</script>

<style scoped>
    th {
        vertical-align: top !important;
    }
    .with-small small{
        display: block;
        color: gray;
        line-height: 1rem;
        overflow: hidden;
    }
    .preview {
        height: 50px;
        border-radius: 5px;
    }
    .additional-filter {
        border-top: 1px solid #DFDFDF;
    }
</style>
