import Services from "../../../scripts/services/services";

export const NAMESPACE = 'serviceNotifications';

export const SET_PAGE = 'set_page';

export const GET_LIST = 'get_list';
export const GET_PAGE_NUMBER = 'get_page_number';
export const GET_TOTAL = 'get_total';
export const GET_PAGE_SIZE = 'get_page_size';
export const GET_NUM_PAGES = 'get_num_pages';

export const ACT_LOAD_PAGE = 'act_load_page';
export const ACT_SAVE_NOTIFICATION = 'act_save_notification';
export const ACT_DELETE_NOTIFICATIONS = 'act_delete_notifications';

export const ACT_LOAD_CHANNELS = 'act_load_channels';

export const ACT_LOAD_NOTIFICATION_TEMPLATES = 'act_load_notification_tempates';
export const ACT_SAVE_NOTIFICATION_TEMPLATE = 'act_save_notification_template';
export const ACT_DELETE_NOTIFICATION_TEMPLATE = 'act_delete_notification_template';

export const ACT_LOAD_NOTIFICATION_ALERT = 'act_load_notification_alert';
export const ACT_SAVE_NOTIFICATION_ALERT = 'act_save_notification_alert';
export const ACT_DELETE_NOTIFICATION_ALERT = 'act_delete_notification_alert';

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
            return Services.net().get(rootGetters.getRoute('communications.service-notification.page'), {page})
                .then(data => {
                    commit(SET_PAGE, {
                        list: data.notifications,
                        total: data.total,
                        page
                    })
                });
        },
        [ACT_SAVE_NOTIFICATION]({rootGetters}, {id, notification}) {
            return Services.net().post(rootGetters.getRoute('communications.service-notification.save'), {}, {id, notification});
        },
        [ACT_DELETE_NOTIFICATIONS]({rootGetters}, {ids}) {
            return Services.net().post(rootGetters.getRoute('communications.service-notification.delete'), {}, {ids});
        },
        [ACT_LOAD_CHANNELS]({rootGetters, commit}) {
            commit('loaderShow', true, {root:true});
            return Services.net().get(rootGetters.getRoute('communications.channels.list'))
                .finally(() => commit('loaderShow', false, {root:true}));
        },
        [ACT_LOAD_NOTIFICATION_TEMPLATES]({rootGetters, commit}, {id}) {
            commit('loaderShow', true, {root:true});
            return Services.net().get(rootGetters.getRoute('communications.service-notification.template.reload', {id}))
                .finally(() => commit('loaderShow', false, {root:true}));
        },
        [ACT_SAVE_NOTIFICATION_TEMPLATE]({rootGetters, commit}, {id, template}) {
            commit('loaderShow', true, {root:true});
            return Services.net().post(rootGetters.getRoute('communications.service-notification.template.save'), {}, {id, template})
                .finally(() => commit('loaderShow', false, {root:true}));
        },
        [ACT_DELETE_NOTIFICATION_TEMPLATE]({rootGetters, commit}, {ids}) {
            commit('loaderShow', true, {root:true});
            return Services.net().post(rootGetters.getRoute('communications.service-notification.template.delete'), {}, {ids})
                .finally(() => commit('loaderShow', false, {root:true}));
        },
        [ACT_LOAD_NOTIFICATION_ALERT]({rootGetters, commit}, {service_notification_id}) {
            commit('loaderShow', true, {root:true});
            return Services.net().get(rootGetters.getRoute('communications.service-notification.system-alert.page', {service_notification_id}))
                .finally(() => commit('loaderShow', false, {root:true}));
        },
        [ACT_SAVE_NOTIFICATION_ALERT]({rootGetters, commit}, {id, alert}) {
            commit('loaderShow', true, {root:true});
            return Services.net().post(rootGetters.getRoute('communications.service-notification.system-alert.save'), {}, {id, alert})
                .finally(() => commit('loaderShow', false, {root:true}));
        },
        [ACT_DELETE_NOTIFICATION_ALERT]({rootGetters, commit}, {ids}) {
            commit('loaderShow', true, {root:true});
            return Services.net().post(rootGetters.getRoute('communications.service-notification.system-alert.delete'), {}, {ids})
                .finally(() => commit('loaderShow', false, {root:true}));
        },
    }
}