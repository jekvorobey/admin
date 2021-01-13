<template>
    <span class="badge" :class="item.badge">
        {{ item.title }}
    </span>
</template>

<script>
export default {
    props: ['status'],
    computed: {
        item() {
            switch (this.status) {
                case 300:
                    return {badge: 'badge-warning badge-warning-light', title: 'приобретен'};
                case 301:
                    return {badge: 'badge-warning', title: 'отправлен'};
                case 302:
                    return {badge: 'badge-success badge-success-light', title: 'активирован'};
                case 303:
                    return {badge: 'badge-success', title: 'используется'};
                case 304:
                    return {badge: 'badge-info', title: 'использован'};
                case 305:
                    return {badge: 'badge-danger', title: 'деактивирован'};
                case 306:
                    return {badge: 'badge-light', title: 'истек срок действия ПС'};
                case 307:
                    return {badge: 'badge-light', title: 'истек срок действия денежных средств'};
                default:
                    return {badge: 'badge-danger', title: 'N/A'};
            }
        },
    }
}
</script>
<style scoped>
.badge-warning-light {
    background: #fcdc6c !important;
}
.badge-success-light {
    background: #63ac73 !important;
}
</style>

