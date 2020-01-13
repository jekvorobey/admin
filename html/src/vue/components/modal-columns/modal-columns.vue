<template>
    <transition name="modal">
        <modal :close="closeModal" v-if="isModalOpen('list_columns')">
            <div slot="header">
                <h3>Управление отображаемыми столбцами</h3>
            </div>
            <div slot="body">
                <div class="row mb-3" v-for="rowColumns in chunkedColumns">
                    <div class="col-md-4" v-for="column in rowColumns">
                        <input type="checkbox" :id="getCheckboxId(column.code)" v-model="column.isShown">
                        <label :for="getCheckboxId(column.code)" class="mb-0">{{column.name}}</label>
                    </div>
                </div>
            </div>
        </modal>
    </transition>
</template>

<script>
    //list_columns
    import {mapGetters} from "vuex";
    import Helpers from '../../../scripts/helpers';

    import Modal from '../controls/modal/modal.vue';
    import modalMixin from '../../mixins/modal.js';

    export default {
        name: "modal-columns",
        components: {
            Modal,
        },
        mixins: [modalMixin],
        props: {
            iColumns: Array,
        },
        data() {
            return {
                columns: this.iColumns,
            };
        },
        methods: {
            getCheckboxId(i) {
                return 'column-' + i;
            }
        },
        computed: {
            ...mapGetters(['getRoute']),
            chunkedColumns() {
                return Helpers.chunkSize(this.columns, 3);
            }
        }
    }
</script>