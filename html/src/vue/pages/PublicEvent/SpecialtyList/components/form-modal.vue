<template>
    <transition name="modal">
        <modal :close="closeModal" v-if="isModalOpen(modalName)">
            <div slot="header">
                {{ specialty.id ? 'Редактирование' : 'Создание' }} направления
            </div>
            <div slot="body">
            <b-form @submit.prevent="accept">
                <b-form-group
                        label="Название"
                        label-for="specialty-name"
                >
                    <b-form-input
                            id="specialty-name"
                            v-model="specialty.name"
                            type="text"
                            required
                            placeholder="Введите название"
                    />
                </b-form-group>

                <b-form-group>
                    <b-form-checkbox v-model="specialty.active" :value="1" :unchecked-value="0">Активность</b-form-checkbox>
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
        channels: Object,
    },
    methods: {
        accept() {
            this.$emit('accept');
        }
    },
    computed: {
        specialty: {
            get() { return this.model; },
            set(value) { this.$emit('update:model', value); }
        }
    }
}
</script>
