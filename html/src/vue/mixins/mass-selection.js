import {mapGetters, mapMutations} from "vuex";
import {
    NAMESPACE,
    GET_ALL,
    GET_EMPTY,
    GET_HAS,
    SET_SELECT,
    SET_DESELECT
} from "../store/modules/mass-selection";

export default {
    computed: {
        ...mapGetters(NAMESPACE, {
            massAll: GET_ALL,
            massEmpty: GET_EMPTY,
            massHas: GET_HAS,
        })
    },
    methods: {
        ...mapMutations(NAMESPACE, {
            massSet: SET_SELECT,
            massUnset: SET_DESELECT
        }),
        massCheckbox(e, type, id) {
            if (e.target.checked) {
                this.massSet({type, id});
            } else {
                this.massUnset({type, id});
            }
        },
    }
}