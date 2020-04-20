import Services from '../../../scripts/services/services';
import Helpers from '../../../scripts/helpers'

export const NAMESPACE = 'publicEvents';

export const SET_DETAIL = 'set_detail';

export const GET_DETAIL = 'get_detail';

export const ACT_IS_UNIQUE = 'act_is_unique';
export const ACT_SAVE_PUBLIC_EVENT = 'act_save_public_event';
export const ACT_LOAD_PUBLIC_EVENT = 'act_load_public_event';
export const ACT_LOAD_AVAILABLE_ORGANIZERS = 'act_load_available_organizers';
export const ACT_SAVE_EVENT_ORGANIZER_ID = 'act_save_event_organizer_id';
export const ACT_SAVE_EVENT_ORGANIZER_VALUE = 'act_save_event_organizer_value';
export const ACT_SAVE_EVENT_MEDIA = 'act_save_event_media';
export const ACT_DELETE_EVENT_MEDIA = 'act_delete_event_media';
export const ACT_LOAD_SPRINTS = 'act_load_sprints';

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
        [ACT_IS_UNIQUE]({rootGetters, commit}, {id, code}) {
            commit('loaderShow', true, {root:true});
            return Services.net().get(rootGetters.getRoute('public-event.isCodeUnique'), {id, code})
                .then(data => data.unique)
                .finally(() => commit('loaderShow', false, {root:true}));
        },
        [ACT_SAVE_PUBLIC_EVENT]({rootGetters, commit}, {id, data}) {
            commit('loaderShow', true, {root:true});
            return Services.net().post(rootGetters.getRoute('public-event.save'), {}, {id, data})
                .finally(() => commit('loaderShow', false, {root:true}));
        },
        [ACT_LOAD_PUBLIC_EVENT]({rootGetters, commit}, {id}) {
            commit('loaderShow', true, {root:true});
            return Services.net().get(rootGetters.getRoute('public-event.load', {event_id: id}))
                .then(data => commit(SET_DETAIL, {publicEvent: data}))
                .finally(() => commit('loaderShow', false, {root:true}));
        },
        [ACT_LOAD_AVAILABLE_ORGANIZERS]({rootGetters, commit}) {
            commit('loaderShow', true, {root:true});
            return Services.net().get(rootGetters.getRoute('public-event.availableOrganizers'))
                .finally(() => commit('loaderShow', false, {root:true}));
        },
        [ACT_SAVE_EVENT_ORGANIZER_ID]({rootGetters, commit}, {publicEventId, organizerId}) {
            commit('loaderShow', true, {root:true});
            return Services.net().post(rootGetters.getRoute('public-event.addOrganizerById', {event_id: publicEventId}), {}, {organizerId})
                .finally(() => commit('loaderShow', false, {root:true}));
        },
        [ACT_SAVE_EVENT_ORGANIZER_VALUE]({rootGetters, commit}, {publicEventId, organizerData}) {
            commit('loaderShow', true, {root:true});
            organizerData.phone = Helpers.rawPhone(organizerData.phone);
            return Services.net().post(rootGetters.getRoute('public-event.addOrganizerByValue', {event_id: publicEventId}), {}, organizerData)
                .finally(() => commit('loaderShow', false, {root:true}));
        },
        [ACT_SAVE_EVENT_MEDIA]({rootGetters, commit}, {publicEventId, type, collection, value, oldMedia}) {
            commit('loaderShow', true, {root:true});
            return Services.net().post(rootGetters.getRoute('public-event.saveMedia', {event_id: publicEventId}), {}, {
                type,
                collection,
                value,
                oldMedia
            })
                .finally(() => commit('loaderShow', false, {root:true}));
        },
        [ACT_DELETE_EVENT_MEDIA]({rootGetters, commit}, {publicEventId, mediaId}) {
            commit('loaderShow', true, {root:true});
            return Services.net().post(rootGetters.getRoute('public-event.deleteMedia', {event_id: publicEventId}), {}, {mediaId})
                .finally(() => commit('loaderShow', false, {root:true}));
        },
        [ACT_LOAD_SPRINTS]({rootGetters, commit}, {publicEventId}) {
            commit('loaderShow', true, {root:true});
            return Services.net().get(rootGetters.getRoute('public-event.getSprints', {event_id: publicEventId}))
                .finally(() => commit('loaderShow', false, {root:true}));
        }
    }
}