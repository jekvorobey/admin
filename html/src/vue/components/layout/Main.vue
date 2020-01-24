<template>
    <div class="fake-vue-body">
        <LayoutHeader :on-index="onIndex"></LayoutHeader>
        <div class="d-flex flex-row middle-area">
            <div style="width: 210px;" class="bg-light" v-if="!isGuest">
                <MainMenu></MainMenu>
            </div>
            <div class="container-fluid flex-grow-1 pb-5 pl-4">
                <div v-if="back" class="mt-3">
                    <span @click="goBack"><fa-icon icon="angle-left"></fa-icon> Назад</span>
                </div>
                <h1 class="mt-3 mb-3" v-if="!hideTitle">{{ title }}</h1>
                <slot></slot>
            </div>
        </div>

        <modal-message></modal-message>
        <LayoutFooter></LayoutFooter>
    </div>
</template>

<script>
    import LayoutFooter from './parts/layout-footer.vue';
    import LayoutHeader from './parts/layout-header.vue';
    import ModalMessage from '../modal-message/modal-message.vue';
    import MainMenu from '../main-menu/main-menu.vue';

    export default {
        name: 'layout-main',
        props: {
            onIndex: { type: Boolean, default: false },
            back: Boolean,
            customTitle: {
                type: String,
                default: ''
            },
            hideTitle: { type: Boolean, default: false },
        },
        components: {
            LayoutFooter,
            LayoutHeader,
            ModalMessage,
            MainMenu,
        },
        methods: {
            goBack() {
                history.back();
            }
        },
        computed: {
            title() {
                return this.customTitle === '' ?  this.$store.state.title : this.customTitle;
            }
        }
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
