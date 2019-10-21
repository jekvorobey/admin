<template>
    <transition name="modal">
        <modal :close="closeModal" v-if="isModalOpen('messageBox')">
            <div slot="header">
                <p v-html="$store.state.modal.messageBox.title"></p>
            </div>
            <div slot="body">
                <p v-html="$store.state.modal.messageBox.text"></p>
            </div>
        </modal>
    </transition>
</template>

<script>
    import modal from '../controls/modal/modal.vue';
    import modalMixin from '../../mixins/modal.js';

    export default {
        mixins: [modalMixin],
        components: {
            modal,
        },
    };
</script>
