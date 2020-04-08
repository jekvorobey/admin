<template>
    <div class="fake-vue-body">
        <LayoutHeader :on-index="onIndex"></LayoutHeader>
        <div class="d-flex flex-row middle-area">
            <div style="width: 210px;" class="bg-light" v-if="!user.isGuest">
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

        <div id="preloader" v-show="loaderShow"><div id="loader"></div></div>
    </div>
</template>

<script>
    import LayoutFooter from './parts/layout-footer.vue';
    import LayoutHeader from './parts/layout-header.vue';
    import ModalMessage from '../modal-message/modal-message.vue';
    import MainMenu from '../main-menu/main-menu.vue';
    import Services from '../../../scripts/services/services.js';

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
                window.location.href = document.referrer;
            }
        },
        computed: {
            title() {
                return this.customTitle === '' ?  this.$store.state.title : this.customTitle;
            },
            loaderShow() {
                return Services.store().state.loaderShow;
            }
        },
        created() {
            Services.event().$on('toast', ({text, variant}) => {
                this.$bvToast.toast(text, {
                    title: 'Сообщение',
                    variant: variant || 'success',
                    //toaster: 'b-toaster-top-center',
                    solid: true,
                    autoHideDelay: 5000,
                });
            });

            let back = this.back;
            window.addEventListener("popstate", function() {
                if (back) {
                    window.location = document.referrer;
                }
            }, false);
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

    /* ============= Loader ===================== */
    #preloader {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 10000;
        background: rgba(255, 255, 255, 0.6);
    }
    #loader {
        display: block;
        position: relative;
        left: 50%;
        top: 50%;
        width: 150px;
        height: 150px;
        margin: -75px 0 0 -75px;
        border-radius: 50%;
        border: 3px solid transparent;
        border-top-color: #000000;
        -webkit-animation: spin 2s linear infinite;
        animation: spin 2s linear infinite;
    }
    #loader:before {
        content: "";
        position: absolute;
        top: 5px;
        left: 5px;
        right: 5px;
        bottom: 5px;
        border-radius: 50%;
        border: 3px solid transparent;
        border-top-color: #000000;
        -webkit-animation: spin 3s linear infinite;
        animation: spin 3s linear infinite;
    }
    #loader:after {
        content: "";
        position: absolute;
        top: 15px;
        left: 15px;
        right: 15px;
        bottom: 15px;
        border-radius: 50%;
        border: 3px solid transparent;
        border-top-color: #000000;
        -webkit-animation: spin 1.5s linear infinite;
        animation: spin 1.5s linear infinite;
    }
    @-webkit-keyframes spin {
        0%   {
            -webkit-transform: rotate(0deg);
            -ms-transform: rotate(0deg);
            transform: rotate(0deg);
        }
        100% {
            -webkit-transform: rotate(360deg);
            -ms-transform: rotate(360deg);
            transform: rotate(360deg);
        }
    }
    @keyframes spin {
        0%   {
            -webkit-transform: rotate(0deg);
            -ms-transform: rotate(0deg);
            transform: rotate(0deg);
        }
        100% {
            -webkit-transform: rotate(360deg);
            -ms-transform: rotate(360deg);
            transform: rotate(360deg);
        }
    }
</style>
