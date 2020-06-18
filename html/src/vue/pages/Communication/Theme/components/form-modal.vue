<template>
    <transition name="modal">
        <modal :close="closeModal" v-if="isModalOpen(modalName)">
            <div slot="header">
                {{ theme.id ? 'Редактирование' : 'Создание' }} темы
            </div>
            <div slot="body">
            <b-form @submit.prevent="accept">
                <b-form-group
                    label="Название"
                    label-for="status-name"
                >
                    <b-form-input
                        id="status-name"
                        v-model="theme.name"
                        type="text"
                        required
                        placeholder="Введите название"
                    />
                </b-form-group>

                <b-form-group>
                    <b-form-checkbox v-model="theme.active" :value="1" :unchecked-value="0">Активность</b-form-checkbox>
                </b-form-group>

                <b-form-group
                        label="Тип темы"
                        label-for="theme-type"
                >
                    <b-form-select v-model="theme.type" class="mb-3" id="theme-type">
                        <b-form-select-option :value="null">Не указан</b-form-select-option>
                        <b-form-select-option :value="type.id" v-for="type in types" :key="type.id">
                            {{ type.name }}
                        </b-form-select-option>
                    </b-form-select>
                </b-form-group>

                <b-form-group
                        label="Канал, в котором может импользоваться тема"
                        label-for="theme-channel"
                >
                    <b-form-select v-model="theme.channel_id" class="mb-3" id="theme-channel">
                        <b-form-select-option :value="null">Общая тема</b-form-select-option>
                        <b-form-select-option :value="channel.id" v-for="channel in channels" :key="channel.id">
                            {{ channel.name }}
                        </b-form-select-option>
                    </b-form-select>
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
        types: Object,
    },
    methods: {
        accept() {
            this.$emit('accept');
        }
    },
    computed: {
        theme: {
            get() { return this.model; },
            set(value) { this.$emit('update:model', value); }
        }
    }
}
</script>
