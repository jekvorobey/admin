<template>
    <layout-main>
        <div class="d-flex justify-content-between mt-3 mb-3">
            <div class="action-bar d-flex justify-content-start">
                <span class="mr-4">Выбрано брендов: {{massAll(massSelectionType).length}}</span>
            </div>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <td></td>
                    <th>ID</th>
                    <th>Логотип</th>
                    <th>Название</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="brand in brands">
                    <td>
                        <input type="checkbox"
                                :checked="massHas({type: massSelectionType, id: brand.id})"
                                @change="e => massCheckbox(e, massSelectionType, brand.id)">
                    </td>
                    <td>{{brand.id}}</td>
                    <td><img :src="fileUrl(brand.file_id)" class="preview"></td>
                    <td>{{brand.name}}</td>
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
    </layout-main>
</template>

<script>
    import withQuery from 'with-query';

    import { mapActions, mapGetters } from 'vuex';

    import {
        ACT_LOAD_PAGE,
        GET_LIST,
        GET_NUM_PAGES,
        GET_PAGE_NUMBER,
        GET_PAGE_SIZE,
        GET_TOTAL,
        NAMESPACE,
        SET_PAGE,
    } from '../../../store/modules/brands';

    import mediaMixin from '../../../mixins/media';
    import massSelectionMixin from '../../../mixins/mass-selection';

    export default {
        mixins: [
            mediaMixin,
            massSelectionMixin,
        ],
        components: {},
        props: {
            iBrands: {},
            iTotal: {},
            iCurrentPage: {},
        },
        data() {
            this.$store.commit(`${NAMESPACE}/${SET_PAGE}`, {
                list: this.iBrands,
                total: this.iTotal,
                page: this.iCurrentPage
            });

            return {
                massSelectionType: 'brands',
            };
        },
        methods: {
            ...mapActions(NAMESPACE, [
                ACT_LOAD_PAGE,
            ]),
            loadPage(page) {
                history.pushState(null, null, location.origin + location.pathname + withQuery('', {
                    page: page,
                }));

                return this[ACT_LOAD_PAGE]({page});
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
                brands: GET_LIST,
            }),
            page: {
                get: function () {
                    return this.GET_PAGE_NUMBER;
                },
                set: function (page) {
                    this.loadPage(page);
                }
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
