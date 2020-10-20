<template>
  <transition name="modal">
    <modal :close="closeModal" v-if="isModalOpen(modalName)">
      <div slot="header">
        Добавление мастер-класса к товару
      </div>
      <div slot="body">
        <v-input v-model="events" class="mb-3"/>
        <button @click="accept" class="btn btn-dark">Добавить</button>
      </div>
    </modal>
  </transition>
</template>

<script>
import modal from '../../../../components/controls/modal/modal.vue';

import VInput from '../../../../components/controls/VInput/VInput.vue';

import modalMixin from '../../../../mixins/modal.js';

export default {
  components: {
    modal,
    VInput,
  },
  mixins: [modalMixin],
  props: {
    modalName: String,
  },
  data () {
    return {
      events: ''
    };
  },
  methods: {
    accept() {
      this.$emit('accept', this.events);
      this.events = '';
      this.closeModal()
    }
  },
}
</script>