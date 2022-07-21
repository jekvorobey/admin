<template>
    <div style="position: relative">
        <fa-icon
                @click="notificationsOpened = !notificationsOpened"
                icon="comment-dots"
                size="lg"
                class="navbar-item navbar-icon">
        </fa-icon>
        <span class="badge badge-pill badge-danger notification-badge" v-if="notificationsCount > 0">{{ notificationsCount }}</span>

        <div v-if="notificationsOpened" class="notifications shadow">
            <button type="button" class="btn btn-link p-2" @click="markNotifications()">Пометить все как прочитанные</button>
            <div class="notifications-wrapper overflow-auto">
                <div class="card mb-2" v-for="notification in notifications">
                    <div class="card-body p-1" :class="notification.status === 2 ? 'notification-old' : ''">
                        <h6 class="card-title mb-0">{{ notification.payload.title }}</h6>
                        <small class="card-subtitle mb-2 text-muted">{{ notification.created_at }}</small>
                        <p v-if="notification.payload.body" class="card-text mt-2 mb-1" v-html="notification.payload.body"></p>
                        <a v-if="notification.payload.url" class="card-link" :href="notification.payload.url">Подробнее...</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import Services from '../../../../scripts/services/services';

    export default {
        name: 'notifications',
        data() {
            return {
                notificationsOpened: false,
                notifications: [],
                notificationsCount: 0,
            }
        },
        methods: {
            getNotifications() {
                const vm = this;
                Services.net().get(this.route('notifications.get'))
                .then(data => {
                    if(data) {
                        vm.notificationsCount = 0;
                        data.forEach(function(item, index) {
                            vm.notifications[index] = item;
                            if(item.status === 1) {
                                vm.notificationsCount++;
                            }
                        })
                    }
                });
            },
            markNotifications() {
                const vm = this;
                Services.net().post(this.route('notifications.markAll'))
                .then(() => {
                    vm.getNotifications();
                });
            },
        },
        mounted() {
            this.getNotifications();

            setInterval(() => {
                this.getNotifications();
            }, 60 * 1000) // 1 Minute
        }
    };
</script>

<style scoped>
    .navbar-item {
        color: white;
        margin-left: 16px;
    }

    .navbar-icon {
        margin-top: 10px;
    }

    .notifications {
        color: black;
        position: fixed;
        right: 0;
        top: 64px;
        width: 300px;
        background: #fff;
        height: 100%;
        border-left: 1px solid #E5E5E5;
        z-index:9999;
    }
    .notifications-wrapper {
        padding: 16px;
        max-height: 90%;
    }
    .notifications-wrapper:not(:last-of-type) {
        border-bottom: 1px solid #E5E5E5;
    }
    .notification-badge {
        position: absolute;
        top: 6px;
        right: -10px;
    }
    .notification-old {
        background: #eee;
        opacity: 0.5;
    }
</style>