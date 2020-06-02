import Services from "../../../scripts/services/services";

export const PROF_NAMESPACE = 'professions';

export const SET_PROFESSIONS = 'set_professions';

export const GET_PROFESSION_LIST = 'get_profesion_list';
export const API_URL = 'https://dev_cm.ibt-mas.greensight.ru/';

export const ACT_PROFESSION_LOAD = 'act_profession_load';

export default {
    name: PROF_NAMESPACE,
    namespaced: true,
    state: {
        list: [],
    },

    mutations: {
        [SET_PROFESSIONS](state, {professions}) {
            state.list = professions;
        }
    },
    getters: {
        [GET_PROFESSION_LIST]: state => state.list
    },
    actions: {
        [ACT_PROFESSION_LOAD]({commit}) {
            return Services.net().get('https://dev_cm.ibt-mas.greensight.ru/api/v1/activities')
                .then(data => {
                    console.log(data)
                    commit(SET_PROFESSIONS, {
                        list: data.items,
                    })
                });
        },
    }
}