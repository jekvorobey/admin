<template>
  <layout-main back>
    <TabDesigns :records="records" @data="setRecords"/>
  </layout-main>
</template>

<script>
import TabDesigns from '../components/tab-designs.vue'

export default {
  components: {TabDesigns},
  props: ['designs'],
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
    this.records = this.designs
  }
}
</script>

<style>
.gift-cards-design-list-preview {
  width: 100px;
  height: 50px;
  background-color: #eee;
  background-size: cover;
  background-repeat: no-repeat;
  border: 1px solid #eee;
  border-radius: 6px;
  display: block;
}

</style>
