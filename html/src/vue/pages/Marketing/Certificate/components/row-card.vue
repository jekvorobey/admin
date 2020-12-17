<template>
  <tr>
    <td><a :href="getRoute('orders.detail', {id: order.order_id})">{{ order.order_number }}</a></td>
    <td>{{ card.id }}</td>
    <td>{{ card.pin }}</td>
    <td>{{ card.price.toLocaleString() }}</td>
    <td>{{ card.balance.toLocaleString() }}</td>
    <td>{{ card.paid_at | datetime}}</td>
    <td>{{ order.notified_at | datetime }}</td>
    <td>{{ card.activated_at | datetime }}</td>
    <td><span class="badge" :class="status.badge">{{ status.title }}</span></td>
    <td><a v-if="customer" :href="customer.url">{{ customer.name }}</a></td>
    <td><a v-if="recipient" :href="recipient.url">{{ recipient.name}}</a></td>
  </tr>
</template>

<script>
export default {
  props: ['card'],
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
    order() {
      return this.card.order || {}
    },
    customer() {
      return (this.card.customer_id)
          ? {
            id: this.card.customer_id,
            name: this.card.customer ? this.card.customer.full_name : this.card.customer_id,
            url: this.getRoute('customers.detail', {id: this.card.customer_id})
          }
          : null
    },
    recipient() {
      return (this.card.recipient_id)
          ? {
            id: this.card.recipient_id,
            name: this.card.recipient ? this.card.recipient.full_name : this.card.recipient_id,
            url: this.getRoute('customers.detail', {id: this.card.recipient_id})
          }
          : null
    },
    status() {
      switch (this.card.status) {
        case 0:   return {badge: 'badge-warning', title: 'новый'};
        case 300: return {badge: 'badge-warning', title: 'приобретен'};
        case 301: return {badge: 'badge-warning', title: 'отправлен'};
        case 302: return {badge: 'badge-success', title: 'активирован'};
        case 303: return {badge: 'badge-success', title: 'используется'};
        case 304: return {badge: 'badge-info', title: 'использован'};
        case 305: return {badge: 'badge-danger', title: 'деактивирован'};
        case 306: return {badge: 'badge-light', title: 'истек срок действия ПС'};
        case 307: return {badge: 'badge-light', title: 'истек срок действия денежных средств'};
        default:  return {badge: 'badge-danger', title: 'N/A'};
      }
    },
  }
}
</script>

