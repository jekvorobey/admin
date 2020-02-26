<template>
    <transition name="modal">
        <modal :close="closeModal" v-if="isModalOpen(modalName)">
            <div slot="header">
                {{ isCreatingAction ? 'Создание': 'Редактирование' }} пункта меню
            </div>
            <div slot="body">
                <b-form @submit.prevent="accept">
                    <b-form-group
                            label="Название*"
                            label-for="item-name"
                    >
                        <b-form-input
                                id="item-name"
                                v-model="menuItem.name"
                                type="text"
                                required
                                placeholder="Введите название"
                        />
                    </b-form-group>

                    <b-form-group
                            label="Ссылка*"
                            label-for="item-url"
                    >
                        <b-form-input
                                id="item-url"
                                v-model="menuItem.url"
                                type="text"
                                required
                                placeholder="Введите ссылку"
                        />
                    </b-form-group>

                    <!-- Option inputs -->

                    <b-button type="submit" variant="dark">Применить</b-button>
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
                get() {
                    return this.model;
                },
                set(value) {
                    this.$emit('update:model', value);
                }
            },
            isCreatingAction() {
                return this.menuItem._new;
            }
        },
    }
</script>
