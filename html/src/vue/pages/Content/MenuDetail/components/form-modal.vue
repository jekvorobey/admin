<template>
    <transition name="modal">
        <modal :close="closeModal" v-if="isModalOpen(modalName)">
            <div slot="header">
                {{ menuItem.id ? 'Редактирование' : 'Создание' }} пункта меню
            </div>
            <div slot="body">
            <b-form @submit.prevent="accept">
                <b-form-group
                    label="Название"
                    label-for="status-name"
                >
                    <b-form-input
                        id="status-name"
                        v-model="menuItem.name"
                        type="text"
                        required
                        placeholder="Введите название"
                    />
                </b-form-group>

                <b-button type="submit" variant="dark">Сохранить</b-button>
            </b-form>
            </div>
        </modal>
    </transition>
</template>

<script>
import modal from '../../../../components/controls/modal/modal.vue';
import modalMixin from '../../../../mixins/modal.js';

export default {
    components: {
        modal,
    },
    mixins: [modalMixin],
    props: {
        modalName: String,
        model: Object,
    },
    methods: {
        accept() {
            this.$emit('accept');
        }
    },
    computed: {
        menuItem: {
            get() { return this.model; },
            set(value) { this.$emit('update:model', value); }
        }
    }
}
</script>
