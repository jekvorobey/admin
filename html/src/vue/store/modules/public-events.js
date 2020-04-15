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
        },
        [ACT_SAVE_EVENT_ORGANIZER_ID]({rootGetters}, {publicEventId, organizerId}) {
            return Services.net().post(rootGetters.getRoute('public-event.addOrganizerById', {event_id: publicEventId}), {}, {organizerId});
        },
        [ACT_SAVE_EVENT_ORGANIZER_VALUE]({rootGetters}, {publicEventId, organizerData}) {
            organizerData.phone = Helpers.rawPhone(organizerData.phone);
            return Services.net().post(rootGetters.getRoute('public-event.addOrganizerByValue', {event_id: publicEventId}), {}, organizerData);
        }
    }
}