import Vue from 'vue';

export const NAMESPACE = 'massSelection';

export const SET_SELECT = 'set_select';
export const SET_DESELECT = 'set_deselect';

export const GET_EMPTY = 'get_empty';
export const GET_HAS = 'get_has';
export const GET_ALL = 'get_all';

export default {
    name: NAMESPACE,
    namespaced: true,
    state: {
        selection: {}
    },
    mutations: {
        [SET_SELECT](state, {type, id}) {
            if (state.selection[type] === undefined) {
                state.selection = {...state.selection, [type]: []}
            }
            state.selection[type].push(id);
        },
        [SET_DESELECT](state, {type, id}) {
            if (state.selection[type] === undefined) {
                return;
            }
            let index = state.selection[type].indexOf(id);
            if (index !== -1) {
                state.selection[type].splice(index, 1);
            }
        }
    },
    getters: {
        [GET_EMPTY]: state => type => state.selection[type] === undefined || state.selection[type].length === 0,
        [GET_HAS]: state => ({type, id}) => {
            if (state.selection[type] === undefined) {
                return false;
            }
            return state.selection[type].indexOf(id) !== -1;
        },
        [GET_ALL]: state => type => {
            if (state.selection[type] === undefined) {
                return [];
            }
            return state.selection[type];
        }
    }
}