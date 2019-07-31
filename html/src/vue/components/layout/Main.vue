<template>
    <div class="fake-vue-body">
        <LayoutHeader :on-index="onIndex"></LayoutHeader>
        <div class="d-flex flex-row middle-area">
            <div style="width: 300px;" class="bg-light" v-if="!isGuest">
                <MainMenu></MainMenu>
            </div>
            <div class="container-fluid flex-grow-1 pb-5">
                <b-breadcrumb :items="breadcrumbsItems" class="breadcrumbs" v-if="breadcrumbsItems.length"></b-breadcrumb>
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
import MainMenu from '../main-menu/main-menu.vue';

export default {
    name: 'layout-main',
    props: {
        onIndex: { type: Boolean, default: false },
    },
    components: {
        LayoutFooter,
        LayoutHeader,
        ModalMessage,
        MainMenu,
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
        background: #fff;
    }
</style>
