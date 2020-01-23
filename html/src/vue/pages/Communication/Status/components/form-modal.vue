<template>
    <transition name="modal">
        <modal :close="closeModal" v-if="isModalOpen(modalName)">
            <div slot="header">
                {{ status.id ? 'Редактирование' : 'Создание' }} статуса
            </div>
            <div slot="body">
            <b-form @submit="accept">
                <b-form-group
                    label="Название"
                    label-for="status-name"
                >
                    <b-form-input
                        id="status-name"
                        v-model="status.name"
                        type="text"
                        required
                        placeholder="Введите названия"
                    />
                </b-form-group>

                <b-form-group>
                    <b-form-checkbox v-model="status.active" :value="1" :unchecked-value="0">Активность</b-form-checkbox>
                </b-form-group>

                <b-form-group>
                    <b-form-checkbox v-model="status.default" :value="1" :unchecked-value="0">По умолчанию</b-form-checkbox>
                </b-form-group>

                <b-form-group>
                    <b-form-select v-model="status.channel_id" class="mb-3">
                        <b-form-select-option :value="null">Please select an option</b-form-select-option>
                    </b-form-select>
                </b-form-group>

                <b-button type="submit" variant="dark">Submit</b-button>
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
        status: {
            get() { return this.model; },
            set(value) { this.$emit('update:model', value); }
        }
    }
}
</script>