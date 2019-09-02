const initialState = {
    currentModal: '',
    messageBox: {
        title: '',
        text: '',
    }
};

export default function (newState = {}) {
    return {
        namespaced: true,
        state: Object.assign(initialState, newState),
        mutations: {
            openModal(state, name) {
                state.currentModal = name;
            },
            setMessage(state, data) {
                state.messageBox = data;
            }
        },
        actions: {
            openModal({commit}, name) {
                commit('openModal', name);
            },
            closeModal({commit}) {
                commit('openModal', '');
            },
            showMessageBox({commit}, data) {
                commit('setMessage', data);
                commit('openModal', 'messageBox');
            }
        },
        getters: {
            isOpen: state => name => {
                return state.currentModal === name;
            }
        }
    };
}