<template>
    <div class="fake-vue-body">
        <LayoutHeader></LayoutHeader>
        <div class="d-flex flex-row middle-area">
            <div style="width: 300px; background: lightgray">
                <!-- sidebar -->
            </div>
            <div class="container-fluid flex-grow-1">
                <b-breadcrumb :items="breadcrumbsItems" class="breadcrumbs"></b-breadcrumb>
                <slot></slot>
            </div>
        </div>

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

<style scoped>
    .fake-vue-body {
        height: 100%;
    }
    .breadcrumbs {
        margin-top: 15px;
    }
    .middle-area {
        height: calc(100% - 106px);
    }
</style>
