<template>
    <layout-main>
        <h1>Test</h1>
        <div>
            <div class="form-group">
                <label for="id">ID</label>
                <input v-model="id" type="text" class="form-control" id="id">
            </div>
            <div class="form-group">
                <label for="email">E-Mail</label>
                <input v-model="email" type="text" class="form-control" id="email">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input v-model="password" type="password" class="form-control" id="password">
            </div>
            <div class="form-group">
                <button @click="doGet" class="btn btn-dark">get</button>
                <button @click="doPost" class="btn btn-dark">post</button>
                <button @click="doPut" class="btn btn-dark">put</button>
                <button @click="doDelete" class="btn btn-dark">delete</button>
            </div>
        </div>

    </layout-main>
</template>

<script>

import Services from "../../../scripts/services/services";

export default {
    props: ['roles'],
    data() {
        return {
            id: 0,
            email: '',
            password: ''
        };
    },
    methods: {
        doGet() {
            let net = Services.net();
            net.get('https://koryukov-auth.ibt-mas.greensight.ru/api/users');
        },
        doPost() {
            let net = Services.net();
            net.post('https://koryukov-auth.ibt-mas.greensight.ru/api/users', {
                email: this.email,
                password: this.password,
            });
        },
        doPut() {
            let net = Services.net();
            net.put('https://koryukov-auth.ibt-mas.greensight.ru/api/users/:id', {id: this.id}, {
                email: this.email
            });
        },
        doDelete() {
            let net = Services.net();
            net.delete('https://koryukov-auth.ibt-mas.greensight.ru/api/users/:id', {id: this.id});
        }
    }
};
</script>
