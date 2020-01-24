<template>
    <a :href="getRoute('communications.chats.unread')" class="btn btn-dark" v-if="count">
        <fa-icon icon="envelope" size="lg"/>
        <span class="badge badge-info">{{ count }}</span>
    </a>
</template>

<script>
import { mapGetters } from 'vuex';
import Services from '../../../../scripts/services/services.js';

export default {
    name: 'communication-chats-unread',
    data() {
        return {
            count: 0,
        }
    },
    methods: {
        loadCount() {
            Services.net().get(this.getRoute('communications.chats.unread.count')).then((data)=> {
                this.count = data.count;
            });
        }
    },
    computed: {
        ...mapGetters(['getRoute']),
    },
    created() {
        this.loadCount();

        setInterval(() => {this.loadCount()}, 1000 * 60)
    }
};
</script>

<style scoped>

</style>