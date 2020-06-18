import Services from '../../../scripts/services/services';
import Helpers from '../../../scripts/helpers'

export const NAMESPACE = 'publicEvents';

export const SET_DETAIL = 'set_detail';
export const SET_PAGE = 'set_page';
export const ACT_LOAD_PAGE = 'act_load_page';

export const GET_DETAIL = 'get_detail';
export const GET_LIST = 'get_list';
export const GET_PAGE_NUMBER = 'get_page_number';
export const GET_TOTAL = 'get_total';
export const GET_PAGE_SIZE = 'get_page_size';
export const GET_NUM_PAGES = 'get_num_pages';


export const ACT_IS_UNIQUE = 'act_is_unique';
export const ACT_SAVE_PUBLIC_EVENT = 'act_save_public_event';
export const ACT_LOAD_PUBLIC_EVENT = 'act_load_public_event';

export const ACT_LOAD_AVAILABLE_ORGANIZERS = 'act_load_available_organizers';
export const ACT_SAVE_EVENT_ORGANIZER_ID = 'act_save_event_organizer_id';
export const ACT_SAVE_EVENT_ORGANIZER_VALUE = 'act_save_event_organizer_value';

export const ACT_SAVE_EVENT_MEDIA = 'act_save_event_media';
export const ACT_DELETE_EVENT_MEDIA = 'act_delete_event_media';

export const ACT_LOAD_SPRINTS = 'act_load_sprints';
export const ACT_SAVE_SPRINT = 'act_save_sprint';
export const ACT_DELETE_SPRINT = 'act_delete_sprint';

export const ACT_LOAD_SPEAKERS = 'act_load_speakers';
export const ACT_LOAD_EVENT_SPEAKERS = 'act_load_event_speakers';
export const ACT_SAVE_EVENT_SPEAKER = 'act_save_event_speaker';
export const ACT_DELETE_EVENT_SPEAKER = 'act_delete_event_speaker';

export const ACT_LOAD_TICKET_TYPES = 'act_load_ticket_types';
export const ACT_SAVE_TICKET_TYPE = 'act_save_ticket_type';
export const ACT_DELETE_TICKET_TYPE = 'act_delete_ticket_type';

export const ACT_LOAD_SPRINT_STAGES = 'act_load_sprint_stages';
export const ACT_SAVE_SPRINT_STAGE = 'act_save_sprint_stage';
export const ACT_DELETE_SPRINT_STAGE = 'act_delete_sprint_stage';

export const ACT_LOAD_PROFESSIONS = 'act_load_professions';
export const ACT_LOAD_EVENT_PROFESSIONS = 'act_load_event_professions';
export const ACT_SAVE_EVENT_PROFESSION = 'act_save_event_profession';
export const ACT_DELETE_EVENT_PROFESSION = 'act_delete_event_profession';

export const ACT_LOAD_PLACES = 'act_load_places';

export const ACT_LOAD_STATUSES = 'act_load_statuses';

export const ACT_LOAD_EVENT_STATUSES = 'act_load_event_statuses';

export const ACT_LOAD_EVENTS = 'act_load_events';

export const ACT_LOAD_EVENT_RECOMMENDATIONS = 'act_load_event_recommendations';
export const ACT_SAVE_EVENT_RECOMMENDATION = 'act_save_event_recommendation';
export const ACT_DELETE_EVENT_RECOMMENDATION = 'act_delete_event_recommendation';

export const ACT_LOAD_SPRINT_RESULTS = 'act_load_sprint_results';
export const ACT_SAVE_SPRINT_RESULT = 'act_save_sprint_result';
export const ACT_DELETE_SPRINT_RESULT = 'act_delete_sprint_result';

export const ACT_SAVE_TICKET_TYPE_STAGE = 'act_save_ticket_type_stage';
export const ACT_DELETE_TICKET_TYPE_STAGE = 'act_delete_ticket_type_stage';

const PAGE_SIZE = 10;

export default {
    name: NAMESPACE,
    namespaced: true,
    state: {
        detail: {},
        list: [],
        page: 1,
        total: 0
    },
    mutations: {
        [SET_DETAIL](state, {publicEvent}) {
            state.detail = publicEvent;
        },
        [SET_PAGE](state, {list, page, total}) {
            state.list = list;
            state.page = page;
            state.total = total;
        }
    },
    getters: {
        [GET_DETAIL]: state => state.detail,
        [GET_LIST]: state => state.list,
        [GET_PAGE_NUMBER]: state => state.page,
        [GET_TOTAL]: state => state.total,
        [GET_PAGE_SIZE]: () => PAGE_SIZE,
        [GET_NUM_PAGES]: state => Math.ceil(state.total / PAGE_SIZE)
    },
    actions: {
        [ACT_LOAD_PAGE]({commit, rootGetters}, {page}) {
            return Services.net().get(rootGetters.getRoute('public-event.list.page'), {page})
                .then(data => {
                    commit(SET_PAGE, {
                        list: data.publicEvents,
                        total: data.total,
                        page
                    })
                });
        },
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
            return Services.net().get(rootGetters.getRoute('public-event.sprints.list'), {event_id: publicEventId})
                .finally(() => commit('loaderShow', false, {root:true}));
        },
        [ACT_SAVE_SPRINT]({rootGetters, commit}, {id, sprint}) {
            commit('loaderShow', true, {root:true});
            return Services.net().post(rootGetters.getRoute('public-event.sprints.save'), {}, {id, sprint})
                .finally(() => commit('loaderShow', false, {root:true}));
        },
        [ACT_DELETE_SPRINT]({rootGetters, commit}, {ids}) {
            commit('loaderShow', true, {root:true});
            return Services.net().post(rootGetters.getRoute('public-event.sprints.delete'), {}, {ids})
                .finally(() => commit('loaderShow', false, {root:true}));
        },
        [ACT_LOAD_TICKET_TYPES]({rootGetters, commit}, {sprintId}) {
            commit('loaderShow', true, {root:true});
            return Services.net().get(rootGetters.getRoute('public-event.ticket-types.list'), {sprint_id: sprintId})
                .finally(() => commit('loaderShow', false, {root:true}));
        },
        [ACT_SAVE_TICKET_TYPE]({rootGetters, commit}, {id, ticketType}) {
            commit('loaderShow', true, {root:true});
            return Services.net().post(rootGetters.getRoute('public-event.ticket-types.save'), {}, {id, ticketType})
                .finally(() => commit('loaderShow', false, {root:true}));
        },
        [ACT_DELETE_TICKET_TYPE]({rootGetters, commit}, {ids}) {
            commit('loaderShow', true, {root:true});
            return Services.net().post(rootGetters.getRoute('public-event.ticket-types.delete'), {}, {ids})
                .finally(() => commit('loaderShow', false, {root:true}));
        },
        [ACT_LOAD_SPEAKERS]({rootGetters, commit}) {
            commit('loaderShow', true, {root:true});
            return Services.net().get(rootGetters.getRoute('public-event.speakers.fullPage'))
                .finally(() => commit('loaderShow', false, {root:true}));
        },
        [ACT_LOAD_EVENT_SPEAKERS]({rootGetters, commit}, {sprintStageId}) {
            commit('loaderShow', true, {root:true});
            return Services.net().get(rootGetters.getRoute('public-event.sprint-stage.getSpeakers', {stage_id: sprintStageId}))
                .finally(() => commit('loaderShow', false, {root:true}));
        },
        [ACT_SAVE_EVENT_SPEAKER]({rootGetters, commit}, {stageId, id}) {
            commit('loaderShow', true, {root:true});
            return Services.net().post(rootGetters.getRoute('public-event.sprint-stage.attachSpeaker', {stage_id: stageId}), {}, {id})
                .finally(() => commit('loaderShow', false, {root:true}));
        },
        [ACT_DELETE_EVENT_SPEAKER]({rootGetters, commit}, {stageId, id}) {
            commit('loaderShow', true, {root:true});
            return Services.net().delete(rootGetters.getRoute('public-event.sprint-stage.detachSpeaker', {stage_id: stageId}), {id})
                .finally(() => commit('loaderShow', false, {root:true}));
        },
        [ACT_LOAD_SPRINT_STAGES]({rootGetters, commit}, {sprintId}) {
            commit('loaderShow', true, {root:true});
            return Services.net().get(rootGetters.getRoute('public-event.sprint-stages.list'), {sprint_id: sprintId})
                .finally(() => commit('loaderShow', false, {root:true}));
        },
        [ACT_SAVE_SPRINT_STAGE]({rootGetters, commit}, {id, sprintStage}) {
            commit('loaderShow', true, {root:true});
            return Services.net().post(rootGetters.getRoute('public-event.sprint-stages.save'), {}, {id, sprintStage})
                .finally(() => commit('loaderShow', false, {root:true}));
        },
        [ACT_DELETE_SPRINT_STAGE]({rootGetters, commit}, {ids}) {
            commit('loaderShow', true, {root:true});
            return Services.net().post(rootGetters.getRoute('public-event.sprint-stages.delete'), {}, {ids})
            .finally(() => commit('loaderShow', false, {root:true}));
        },
        [ACT_LOAD_EVENT_PROFESSIONS]({rootGetters, commit}, {publicEventId}) {
            commit('loaderShow', true, {root:true});
            return Services.net().get(rootGetters.getRoute('public-event.event.getProfessions', {event_id: publicEventId}))
                .finally(() => commit('loaderShow', false, {root:true}));
        },
        [ACT_LOAD_PROFESSIONS]({rootGetters, commit}) {
            commit('loaderShow', true, {root:true});
            return Services.net().get(rootGetters.getRoute('public-event.professions.names'))
                .finally(() => commit('loaderShow', false, {root:true}));
        },
        [ACT_SAVE_EVENT_PROFESSION]({rootGetters, commit}, {publicEventProfession}) {
            commit('loaderShow', true, {root:true});
            return Services.net().post(rootGetters.getRoute('public-event.professions.save'), {}, {publicEventProfession})
                .finally(() => commit('loaderShow', false, {root:true}));
        },
        [ACT_DELETE_EVENT_PROFESSION]({rootGetters, commit}, {ids}) {
            commit('loaderShow', true, {root:true});
            return Services.net().post(rootGetters.getRoute('public-event.professions.delete'), {}, {ids})
                .finally(() => commit('loaderShow', false, {root:true}));
        },
        [ACT_LOAD_PLACES]({rootGetters, commit}) {
            commit('loaderShow', true, {root:true});
            return Services.net().get(rootGetters.getRoute('public-event.places.fullList'))
                .finally(() => commit('loaderShow', false, {root:true}));
        },
        [ACT_LOAD_STATUSES]({rootGetters, commit}) {
            commit('loaderShow', true, {root:true});
            return Services.net().get(rootGetters.getRoute('public-event.statuses.list'))
                .finally(() => commit('loaderShow', false, {root:true}));
        },
        [ACT_LOAD_EVENTS]({rootGetters, commit}) {
            commit('loaderShow', true, {root:true});
            return Services.net().get(rootGetters.getRoute('public-event.fullList'))
                .finally(() => commit('loaderShow', false, {root:true}));
        },
        [ACT_LOAD_EVENT_RECOMMENDATIONS]({rootGetters, commit}, {publicEventId}) {
            commit('loaderShow', true, {root:true});
            return Services.net().get(rootGetters.getRoute('public-event.recommendations', {event_id: publicEventId}))
                .finally(() => commit('loaderShow', false, {root:true}));
        },
        [ACT_SAVE_EVENT_RECOMMENDATION]({rootGetters, commit}, {event_id, recommendation_id}) {
            commit('loaderShow', true, {root:true});
            return Services.net().post(rootGetters.getRoute('public-event.attachRecommendation', {event_id, recommendation_id}))
                .finally(() => commit('loaderShow', false, {root:true}));
        },
        [ACT_DELETE_EVENT_RECOMMENDATION]({rootGetters, commit}, {event_id, recommendation_id}) {
            commit('loaderShow', true, {root:true});
            return Services.net().delete(rootGetters.getRoute('public-event.detachRecommendation', {event_id, recommendation_id}))
                .finally(() => commit('loaderShow', false, {root:true}));
        },
        [ACT_LOAD_SPRINT_RESULTS]({rootGetters, commit}, {sprintId}) {
            commit('loaderShow', true, {root:true});
            return Services.net().get(rootGetters.getRoute('public-event.sprint-documents.list'), {sprint_id: sprintId})
                .finally(() => commit('loaderShow', false, {root:true}));
        },
        [ACT_SAVE_SPRINT_RESULT]({rootGetters, commit}, {id, sprintDocument}) {
            commit('loaderShow', true, {root:true});
            return Services.net().post(rootGetters.getRoute('public-event.sprint-documents.save'), {}, {id, sprintDocument})
                .finally(() => commit('loaderShow', false, {root:true}));
        },
        [ACT_DELETE_SPRINT_RESULT]({rootGetters, commit}, {ids}) {
            commit('loaderShow', true, {root:true});
            return Services.net().post(rootGetters.getRoute('public-event.sprint-documents.delete'), {}, {ids})
                .finally(() => commit('loaderShow', false, {root:true}));
        },
        [ACT_LOAD_EVENT_STATUSES]({rootGetters, commit}) {
            commit('loaderShow', true, {root:true});
            return Services.net().get(rootGetters.getRoute('public-event.event-statuses.list'))
                .finally(() => commit('loaderShow', false, {root:true}));
        },
        [ACT_SAVE_TICKET_TYPE_STAGE]({rootGetters, commit}, {id, stage_id}) {
            commit('loaderShow', true, {root:true});
            return Services.net().post(rootGetters.getRoute('public-event.ticket-types.attachStage', {stage_id}), {id})
                .finally(() => commit('loaderShow', false, {root:true}));
        },
        [ACT_DELETE_TICKET_TYPE_STAGE]({rootGetters, commit}, {id, stage_id}) {
            commit('loaderShow', true, {root:true});
            return Services.net().delete(rootGetters.getRoute('public-event.ticket-types.detachStage', {stage_id}), {id})
                .finally(() => commit('loaderShow', false, {root:true}));
        },
    }
}