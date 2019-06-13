<template>
    <nav class="navbar navbar-dark bg-dark" style="color: #dfdfdf;">
        <a class="navbar-brand">MAS</a>

        <a v-if="isGuest" :href="route('login')" class="btn btn-danger">Не авторизован</a>
        <button v-else @click="logout" class="btn btn-success">Авторизован</button>
    </nav>
</template>

<script>
import axios from 'axios';

export default {
    name: 'layout-header',
    data() {
        return {};
    },
    methods: {
        logout() {
            axios.get(this.route('logout-ajax'))
                .then(() => {
                    window.location.href = this.route('home');
                })
        }
    },
};
</script>
