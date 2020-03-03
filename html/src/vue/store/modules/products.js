import Services from "../../../scripts/services/services";

export const NAMESPACE = 'products';

export const SET_PAGE = 'set_page';

export const GET_LIST = 'get_list';

export const ACT_LOAD_PAGE = 'act_load_page';

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
        }
    }
}