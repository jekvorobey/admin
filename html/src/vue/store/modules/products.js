import Services from "../../../scripts/services/services";

export const NAMESPACE = 'products';

export const SET_PAGE = 'set_page';

export const GET_LIST = 'get_list';
export const GET_PAGE_NUMBER = 'get_page_number';
export const GET_TOTAL = 'get_total';
export const GET_PAGE_SIZE = 'get_page_size';
export const GET_NUM_PAGES = 'get_num_pages';

export const ACT_LOAD_PAGE = 'act_load_page';
export const ACT_UPDATE_APPROVAL = 'act_update_approval';
export const ACT_UPDATE_PRODUCTION = 'act_update_production';
export const ACT_UPDATE_ARCHIVE = 'act_update_archive';

const PAGE_SIZE = 10;

export default {
    name: NAMESPACE,
    namespaced: true,
    state: {
        list: [],
        page: 1,
        total: 0
    },
    mutations: {
        [SET_PAGE](state, {list, page, total}) {
            state.list = list;
            state.page = page;
            state.total = total;
        }
    },
    getters: {
        [GET_LIST]: state => state.list,
        [GET_PAGE_NUMBER]: state => state.page,
        [GET_TOTAL]: state => state.total,
        [GET_PAGE_SIZE]: () => PAGE_SIZE,
        [GET_NUM_PAGES]: state => Math.ceil(state.total / PAGE_SIZE)
    },
    actions: {
        [ACT_LOAD_PAGE]({commit, rootGetters}, {filter, page}) {
            return Services.net().get(rootGetters.getRoute('products.listPage'), {page, filter})
                .then(data => {
                    commit(SET_PAGE, {
                        list: data.products,
                        total: data.total,
                        page
                    })
                });
        },
        [ACT_UPDATE_APPROVAL]({rootGetters}, {ids, status, comment}) {
            return Services.net().put(rootGetters.getRoute('products.massApproval'), {}, {
                productIds: ids,
                status,
                comment
            });
        },
        [ACT_UPDATE_PRODUCTION]({rootGetters}, {ids, status, comment}) {
            return Services.net().put(rootGetters.getRoute('products.massProduction'), {}, {
                productIds: ids,
                status,
                comment
            });
        },
        [ACT_UPDATE_ARCHIVE]({rootGetters}, {ids, status, comment}) {
            return Services.net().put(rootGetters.getRoute('products.massArchive'), {}, {
                productIds: ids,
                status,
                comment
            });
        },
    }
}