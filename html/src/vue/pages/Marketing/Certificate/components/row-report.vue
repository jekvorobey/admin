<template>
    <tr>
        <td>{{ item.id }}</td>
        <td>{{ item.created_at | datetime }}</td>
        <td>
          <a :href="downloadLink">Скачать отчет</a>
        </td>
        <td v-if="canUpdate(blocks.settings)">
            <a :href="creatorLink" target="_blank">{{creatorName}}</a>
        </td>
        <td v-else>
            {{ creatorName }}
        </td>
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
      },
      creatorLink() {
        return this.getRoute('settings.userDetail', {id: this.item.creator_id})
      },
      creatorName() {
          return (this.item.creator && this.item.creator.short_name) ? this.item.creator.short_name : this.item.creator_id
      }
    }
}
</script>
