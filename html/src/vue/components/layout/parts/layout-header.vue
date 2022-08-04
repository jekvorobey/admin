<template>
    <header class="navbar navbar-dark bg-dark" style="color: #dfdfdf;">
        <div class="d-flex align-items-center">
            <div class="d-xl-none">
                <menu-btn></menu-btn>
            </div>
            <b-navbar-brand href="/" title="Бессовестно Талантливый">
                <v-svg
                    name="header-logo"
                    modifier="fill-white"
                    width="24"
                    height="24"
                />
            </b-navbar-brand>
            <div class="divider d-none d-md-block"></div>
            <span class="logo-text d-none d-md-block">Administration System</span>
        </div>

        <div v-if="!user.isGuest" class="d-flex align-items-baseline">
            <notifications/>

            <communication-chats-unread/>

            <button @click="logout" class="btn btn-dark">
                <v-svg
                    name="user-logout"
                    modifier="fill-white"
                    width="24"
                    height="24"
                />
            </button>
        </div>
    </header>
</template>

<script>
    import '../../../../images/sprite/header-logo.svg'
    import '../../../../images/sprite/user-logout.svg'
    import Services from '../../../../scripts/services/services';
    import modalMixin from '../../../mixins/modal.js';
    import CommunicationChatsUnread from './communication-chats-unread.vue';
    import Notifications from './notifications.vue';
    import VSvg from '../../controls/VSvg/VSvg.vue';
    import MainMenu from '../../main-menu/main-menu.vue';
    import MenuBtn from '../../main-menu/menu-btn.vue';

    export default {
    name: 'layout-header',
    components: {Notifications, CommunicationChatsUnread, VSvg, MainMenu, MenuBtn},
    mixins: [modalMixin],
    props: {
        onIndex: { type: Boolean, default: false },
    },
    data() {
        return {};
    },
    methods: {
        logout() {
            Services.net().post(this.route('logout'))
                .then(() => {
                    window.location.href = this.route('page.login');
                }, () => {
                    this.showMessageBox({title: 'Ошибка', text: 'Произошла ошибка. Попробуйте позже.'})
                })
        }
    },
};
</script>

<style scoped>
    .navbar{
        padding: 0.5rem 1rem 0.7rem 1rem;
    }
    .divider{
        height: 40px;
        width: 2px;
        background-color: #fff;
        margin-right: 10px;
    }
    .logo-text{
        padding-top: 4px;
        color: white;
    }
</style>