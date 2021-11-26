<template>
  <layout-main>
    <f-input v-model="from" class="col-lg-3 col-md-6 col-sm-12">Источник</f-input>
    <f-input v-model="to" class="col-lg-3 col-md-6 col-sm-12">Результат</f-input>
    <div class="mb-3" v-if="canUpdate(blocks.content)">
      <button @click="" class="btn btn-success">Создать</button>
    </div>
  </layout-main>
</template>

<script>
import FInput from '../../../components/filter/f-input.vue';
export default {
  components: {
    FInput
  },
  data() {
    return {
      from: '',
      to: ''
    }
  }
}
</script>