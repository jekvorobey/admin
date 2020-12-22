<template>
    <tr>
        <td>{{ item.id }}</td>
        <td>
            <a v-if="entity.link" :href="entity.link">
                {{ entity.title }}
            </a>
            <span v-else>{{ entity.title }}</span>
        </td>
        <td>{{ eventText }}</td>
        <td>
            <a v-if="item.user" :href="getRoute('settings.userDetail', { id: item.user_id })">
                {{ item.user.short_name }}
            </a>
          <div v-else>{{ item.user_id }}</div>
        </td>
        <td>{{ item.created_at | datetime }}</td>
    </tr>
</template>

<script>
export default {
    props: ['item'],
    filters: {
        datetime(value) {
            if (value) {
                const parts = value.split(' ');
                if (parts.length === 2) {
                    return parts[0].split('-').reverse().join('.') + ' ' + parts[1];
                }
            }
            return value;
        },
    },
    computed: {
        entity() {
            const id = this.item.entity_id;
            const type = this.item.entity_type;
            switch (type) {
                case 'App\\Models\\Certificate\\Nominal':
                    return {
                        id,
                        title: `Номинал #${id}`,
                        link: this.getRoute('certificate.nominals_edit', {id})
                    }
                case 'App\\Models\\Certificate\\Design':
                    return {
                        id,
                        title: `Дизайн #${id}`,
                        link: this.getRoute('certificate.designs_edit', {id})
                    }
                case 'App\\Models\\Certificate\\Card':
                    return {
                        id,
                        title: `ПС #${id}`,
                        link: null
                    }
                case 'App\\Models\\Certificate\\Order':
                    const e = (this.item && this.item.entity) ? this.item.entity : {}
                    console.log(this.item)
                    const orderId = e.order_number || id;
                    return {
                        id,
                        title: `Заказ №${orderId}`,
                        link: (e.order_id) ? this.getRoute('orders.detail', { id: e.order_id }) : null,
                    }
                default:
                    return {
                        id,
                        title: `${type} #${id}`,
                        link: null
                    }
            }
        },

        eventText() {
            switch (this.item.event) {
                case 'created': return "Создание"
                case 'deleted': return "Удаление"
                default: return "Изменение"
            }
        }
    },
}
</script>
