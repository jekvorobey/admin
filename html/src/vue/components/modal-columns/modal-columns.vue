<template>
    <transition name="modal">
        <modal :close="closeModal" v-if="isModalOpen('columnsBox')">
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
import Helpers from '../../../scripts/helpers';

import modal from '../controls/modal/modal.vue';
import modalMixin from '../../mixins/modal.js';

export default {
    mixins: [modalMixin],
    components: {
        modal,
    },
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
        chunkedColumns() {
            return Helpers.chunkSize(this.columns, 3);
        }
    }
}
</script>
