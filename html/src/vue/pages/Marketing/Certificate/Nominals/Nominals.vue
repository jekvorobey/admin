<template>
  <layout-main back>
    <TabNominal :records="records" @data="setRecords" />
  </layout-main>
</template>

<script>
import TabNominal from '../components/tab-nominals.vue'

export default {
  components: {TabNominal},
  props: ['nominals'],
  data() {
    return {
      records: null
    }
  },
  methods: {
    setRecords(records) {
      this.records = records
    },
  },
  created() {
    this.records = this.nominals
  }
}
</script>
