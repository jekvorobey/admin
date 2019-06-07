<template>
    <div>
        <LayoutHeader></LayoutHeader>
        <slot></slot>
        <modal-message></modal-message>
        <LayoutFooter></LayoutFooter>
    </div>
</template>

<script>
import LayoutFooter from '../layout-footer/layout-footer.vue';
import LayoutHeader from '../layout-header/layout-header.vue';
import ModalMessage from '../modal-message/modal-message.vue';

export default {
    name: 'layout-main',
    props: {
        onIndex: { type: Boolean, default: false },
    },
    components: {
        LayoutFooter,
        LayoutHeader,
        ModalMessage,
    },
};
</script>
