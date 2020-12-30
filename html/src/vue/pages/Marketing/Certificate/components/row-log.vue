<template>
    <tr>
        <td>{{ item.id }}</td>
        <td>
            <a v-if="entity.link" :href="entity.link">
                {{ entity.title }}
            </a>
            <span v-else>{{ entity.title }}</span>
        </td>
        <td><a href="#" @click.prevent="showDetails">{{ eventText }}</a></td>
        <td>
            <a v-if="item.user" :href="getRoute('settings.userDetail', { id: item.user_id })">
                {{ item.user.short_name }}
            </a>
          <div v-else>{{ item.user_id }}</div>
        </td>
        <td>{{ item.created_at | datetime }}
          <ModalWindow v-if="showModal" type="wide" :close="closeDetails">
            <div slot="header">Лог запись № {{ item.id}}</div>
            <div slot="body">
              <table style="width: 100%">
                <tr>
                  <th style="width: 30%">Ключ</th>
                  <th style="width: 35%">Старое значение</th>
                  <th style="width: 35%">Новое значение значение</th>
                </tr>
                <tr v-for="change in changes" :key="change.key">
                  <td>{{ change.key }}</td>
                  <td>{{ change.old }}</td>
                  <td>{{ change.new }}</td>
                </tr>
              </table>
            </div>

          </ModalWindow>
        </td>
    </tr>
</template>

<script>
import ModalWindow from '../../../../components/controls/modal/modal.vue'
export default {
    props: ['item'],
    components: {ModalWindow},
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
    data () {
      return {
        showModal: false
      }
    },
    computed: {
        changes() {
          const oldValues = (this.item.data && this.item.data.old) ? this.item.data.old : {}
          const newValues = (this.item.data && this.item.data.new) ? this.item.data.new : {}

          let changes = {}
          for (let key in oldValues) {
            changes[key] = {
              key: key,
              old: oldValues[key],
              new: newValues[key] || ''
            }
          }
          for (let key in newValues) {
            if (changes.hasOwnProperty(key))
              continue;
            changes[key] = {
              key: key,
              old: '',
              new: newValues[key]
            }
          }

          return changes
        },
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
    methods: {
      showDetails() {
        this.showModal = true
      },
      closeDetails() {
        this.showModal = false
      }
    }
}
</script>
