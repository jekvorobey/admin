<template>
    <layout-main>
        <table class="table">
            <thead>
            <tr class="table-secondary">
                <th colspan="2" style="text-align: left">
                    <button class="btn btn-light btn-bg"
                            @click="openBadgesEditModal(null)"
                            disabled>
                        <fa-icon icon="check"/> Порядок сохранён
                    </button>
                </th>
                <th colspan="2" style="text-align: right">
                    <button class="btn btn-success btn-bg"
                            @click="openBadgesEditModal(null)">
                        Добавить шильдик <fa-icon icon="plus"/>
                    </button>
                </th>
            </tr>
            <tr>
                <th>№ П/п</th>
                <th>Текст шильдика</th>
                <th>Тип шильдика</th>
                <th style="text-align: right">Действия</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="(badge, index) in badges">
                <td>
                    <h5>{{ badge.order_num }}.</h5>
                </td>
                <td>
                    <h5>
                        <span class="badge badge-dark">
                            {{ badge.text }}
                        </span>
                    </h5>
                </td>
                <td>
                    <span class="badge"
                          :class="typeClass(badge.type)">
                        {{ badge_types[badge.type] || 'Другое' }}
                    </span>
                </td>
                <td align="right">
                    <button class="btn btn-info btn-sm">
                        Изменить <fa-icon icon="pencil-alt"/>
                    </button>

                    <button class="btn btn-danger btn-sm">
                        Удалить <fa-icon icon="trash-alt"/>
                    </button>
                </td>
            </tr>
            </tbody>
        </table>

        <modal-edit-form

                modal-name="EditBadgeModal"/>
    </layout-main>
</template>

<script>
    import Services from "../../../../scripts/services/services";
    import modalMixin from '../../../mixins/modal';
    import Modal from '../../../components/controls/modal/modal.vue';
    import ModalEditForm from "./components/modal-edit-form.vue"
    import VDeleteButton from "../../../components/controls/VDeleteButton/VDeleteButton.vue";

    export default {
        components: {
            Modal,
            VDeleteButton,
            ModalEditForm
        },
        mixins: [modalMixin],
        props: [
            'iBadges',
            'iBadgesTypes'
        ],
        data() {
            return {
                badges: this.iBadges,
                badge_types: this.iBadgesTypes,
            };
        },
        methods: {

            openBadgesEditModal: async function(badgeId) {
                /*this.badgeToEdit = badgeId ?
                    this.badges[badgeId] : ''
                await this.$nextTick();*/
                this.openModal('EditBadgeModal')
            },
            typeClass(typeId) {
                switch (typeId) {
                    case 1: return 'badge-warning';
                    case 2: return 'badge-success';
                    case 3: return 'badge-secondary';
                    default: return 'badge-light';
                }
            },
        },
    }
</script>

<style scoped>

</style>