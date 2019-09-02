<template>
    <nav class="navbar navbar-dark bg-dark" style="color: #dfdfdf;">

        <div class="d-flex">
            <span>
                <picture>
                    <source srcset="/assets/images/logo_white.webp" type="image/webp">
                    <img src="/assets/images/logo_white.png" width="128" height="46">
                </picture>
            </span>
            <span style="
                font-size: 256%;
                line-height: 40px;
                margin-right: 10px;
                font-weight: 700;
                transform: scaleX(0.5);
            ">|</span>
            <span style="color:white">
                Administration System
            </span>
        </div>

        <button v-if="!isGuest" @click="logout" class="btn btn-dark">Выйти</button>
    </nav>
</template>

<script>
import '../../../images/logo_white.png';
import Services from '../../../scripts/services/services';
import modalMixin from '../../mixins/modal.js';

export default {
    name: 'layout-header',
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
