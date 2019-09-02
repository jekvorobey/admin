import {mapActions, mapGetters} from "vuex";

export default {
    methods: {
        ...mapActions({
            openModal: 'modal/openModal',
            closeModal: 'modal/closeModal',
            showMessageBox: 'modal/showMessageBox',
        })
    },
    computed: {
        ...mapGetters({
            isModalOpen: 'modal/isOpen'
        })
    }
}