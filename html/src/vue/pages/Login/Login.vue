<template>
    <layout-main>
        <h1>Login</h1>
        <div>
            <div class="form-group">
                <label for="email">E-mail</label>
                <input v-model="email" type="text" id="email">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input v-model="password" type="password" id="password">
            </div>
            <div class="form-group">
                <button @click="login">Log In</button>
            </div>
        </div>
    </layout-main>
</template>

<script>
import axios from 'axios';
export default {
    name: 'page-login',
    components: {},
    props: [],
    data() {
        return {
            email: '',
            password: '',
        };
    },
    methods: {
        login() {
            axios.get(this.route('login-ajax'), {
                params: {
                    email: this.email,
                    password: this.password
                }
            }).then(() => {
                window.location.href = this.route('home')
            });
        }
    }
};
</script>
