<template>
    <tr>
        <td>{{ item.id }}</td>
        <td>{{ item.created_at | datetime }}</td>
        <td>
          <a :href="downloadLink">Скачать отчет</a>
        </td>
        <td>{{ item.creator_id }}</td>
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
    methods: {
      printReport() {
        window.print();
      }
    },
    computed: {
      downloadLink() {
        return this.getRoute('certificate.report_download', {id: this.item.id})
      }
    }
}
</script>
