<template>
  <b-row class="mb-2">
    <b-col>
      <b-card>
        <table class="table table-sm">
          <thead>
          <tr>
            <th colspan="4">KPIs</th>
          </tr>
          </thead>
          <tbody>
          <tr>
            <th>Продано всего сертификатов кол-во на сумму</th>
            <td>{{  kpis.total }}</td>
          </tr>
          <tr>
            <th>Активировано всего кол-во на сумму</th>
            <td>{{ kpis.activated }}</td>
          </tr>
          <tr>
            <th>Использовано всего кол-во на сумму</th>
            <td>{{ kpis.used }}</td>
          </tr>
          <tr>
            <th>Остатки на счетах владельцев на сумму</th>
            <td>{{ kpis.balance }}</td>
          </tr>
          <tr>
            <th>Количество заказов с ПС</th>
            <td>{{ kpis.orders_amount }}</td>
          </tr>
          </tbody>
        </table>
      </b-card>
    </b-col>
  </b-row>
</template>

<script>
export default {
  props: {
    kpis: {
      type: Object,
      default: () => ({})
    }
  }
}
</script>

