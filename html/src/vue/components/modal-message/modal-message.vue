<template>
    <transition name="modal">
        <modal :close="() => this.changeModal('modal_message', false)" v-if="show">
            <div slot="body">
                <p v-html="text"></p>
            </div>
        </modal>
    </transition>
</template>

<script>
import modal from '../controls/modal/modal.vue';

export default {
    name: 'modal-message',
    components: {
        modal,
    },
    computed: {
        show() {
            return this.$store.state.modals.message;
        },
        text() {
            return this.$store.state.modal_message;
        },
    },
};
</script>
