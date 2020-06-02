import Services from "../../../scripts/services/services";

export const NAMESPACE = 'types';

export const SET_PAGE = 'set_page';

export const GET_LIST = 'get_list';
export const GET_PAGE_NUMBER = 'get_page_number';
export const GET_TOTAL = 'get_total';
export const GET_PAGE_SIZE = 'get_page_size';
export const GET_NUM_PAGES = 'get_num_pages';

export const ACT_LOAD_PAGE = 'act_load_page';
export const ACT_SAVE_TYPES = 'act_save_types';
export const ACT_DELETE_TYPES = 'act_delete_types';

const PAGE_SIZE = 10;

export default {
    name: NAMESPACE,
    namespaced: true,
    state: {
        list: [],
        page: 1,
        total: 0,
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
        [ACT_LOAD_PAGE]({commit, rootGetters}, {page}) {
            return Services.net().get(rootGetters.getRoute('public-event.types.page'), {page})
                .then(data => {
                    commit(SET_PAGE, {
                        list: data.types,
                        total: data.total,
                        page
                    })
                });
        },
        [ACT_SAVE_TYPES]({rootGetters}, {id, type}) {
            return Services.net().post(rootGetters.getRoute('public-event.types.save'), {}, {id, type});
        },
        [ACT_DELETE_TYPES]({rootGetters}, {ids}) {
            return Services.net().post(rootGetters.getRoute('public-event.types.delete'), {}, {ids});
        }
    }
}