<template>
    <header class="navbar navbar-dark bg-dark" style="color: #dfdfdf;">
        <div class="d-flex">
            <b-navbar-brand href="/" title="Бессовестно Талантливый">
                <picture>
                    <source srcset="/assets/images/logo_white.webp" type="image/webp">
                    <img src="/assets/images/logo_white.png" width="128" height="46">
                </picture>
            </b-navbar-brand>
            <span class="d-none d-md-block">
                <span style="
                    font-size: 256%;
                    line-height: 40px;
                    margin-right: 10px;
                    transform: scaleX(0.5);
                ">|</span>
                <span style="color:white">
                    Administration System
                </span>
            </span>
        </div>

        <div v-if="!user.isGuest" class="d-flex">
            <notifications/>

            <communication-chats-unread/>

            <button @click="logout" class="btn btn-dark">Выйти</button>
        </div>
    </header>
</template>

<script>
    import '../../../../images/logo_white.png';
    import Services from '../../../../scripts/services/services';
    import modalMixin from '../../../mixins/modal.js';
    import CommunicationChatsUnread from './communication-chats-unread.vue';
    import Notifications from './notifications.vue';

    export default {
    name: 'layout-header',
    components: {Notifications, CommunicationChatsUnread},
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
<style>

</style>
