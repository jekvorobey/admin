<template>
    <div class="fake-vue-body">
        <LayoutHeader :on-index="onIndex"></LayoutHeader>
        <div class="container-fluid">
            <div class="row flex-xl-nowrap">
                <div class="bg-light col-xl-2 no-padding w-20" v-if="!user.isGuest">
                    <MainMenu></MainMenu>
                </div>
                <main class="flex-grow-1 no-padding" :class="!user.isGuest ? 'col-xl-10' : 'col-xl-12'">
                    <div class="container-fluid px-3 pb-5">
                        <div v-if="back" class="mt-3">
                            <span class="button-back" @click="goBack"><fa-icon icon="angle-left"></fa-icon> Назад</span>
                        </div>
                        <h1 class="mt-3 mb-3" v-if="!hideTitle">{{ title }}</h1>
                        <slot></slot>
                        <div class="clearfix"></div>
                    </div>
                </main>
            </div>

            <modal-message></modal-message>

            <div id="preloader" v-show="loaderShow"><div id="loader"></div></div>
        </div>
    </div>
</template>

<script>
    import LayoutHeader from './parts/layout-header.vue';
    import ModalMessage from '../modal-message/modal-message.vue';
    import MainMenu from '../main-menu/main-menu.vue';
    import Services from '../../../scripts/services/services.js';

    export default {
        name: 'layout-main',
        props: {
            onIndex: { type: Boolean, default: false },
            back: Boolean,
            backUrl: String,
            customTitle: {
                type: String,
                default: ''
            },
            hideTitle: { type: Boolean, default: false },
        },
        components: {
            LayoutHeader,
            ModalMessage,
            MainMenu,
        },
        methods: {
            goBack() {
                if (document.referrer) {
                    const referrer = new URL(document.referrer);
                    if (referrer.host === location.host) {
                        window.location.href = referrer;
                    }
                }
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
                    window.location.href = document.referrer;
                }
            }, false);
        },
    };
</script>

<style scoped>
    .w-20{
        min-width: 20%;
    }
    .fake-vue-body {
        height: 100%;
    }
    .no-padding {
        padding: 0 !important;
        margin: 0 !important;
    }
    .button-back {
        cursor: pointer;
    }
    .button-back:hover {
        opacity: 0.8;
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
