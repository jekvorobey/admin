import Services from "../../../scripts/services/services";

export const NAMESPACE = 'publicEvents';

export const SET_DETAIL = 'set_detail';

export const GET_DETAIL = 'get_detail';

export const ACT_IS_UNIQUE = 'act_is_unique';
export const ACT_SAVE_PUBLIC_EVENT = 'act_save_public_event';
export const ACT_LOAD_PUBLIC_EVENT = 'act_load_public_event';
export const ACT_LOAD_AVAILABLE_ORGANIZERS = 'act_load_available_organizers';

export default {
    name: NAMESPACE,
    namespaced: true,
    state: {
        detail: {}
    },
    mutations: {
        [SET_DETAIL](state, {publicEvent}) {
            state.detail = publicEvent;
        }
    },
    getters: {
        [GET_DETAIL]: state => state.detail,
    },
    actions: {
        [ACT_IS_UNIQUE]({rootGetters}, {id, code}) {
            return Services.net().get(rootGetters.getRoute('public-event.isCodeUnique'), {id, code})
                .then(data => data.unique);
        },
        [ACT_SAVE_PUBLIC_EVENT]({rootGetters}, {id, data}) {
            return Services.net().post(rootGetters.getRoute('public-event.save'), {}, {id, data})
        },
        [ACT_LOAD_PUBLIC_EVENT]({rootGetters, commit}, {id}) {
            return Services.net().get(rootGetters.getRoute('public-event.load', {event_id: id}))
                .then(data => commit(SET_DETAIL, {publicEvent: data}));
        },
        [ACT_LOAD_AVAILABLE_ORGANIZERS]({rootGetters}) {
            return Services.net().get(rootGetters.getRoute('public-event.availableOrganizers'));
        }
    }
}