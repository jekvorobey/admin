<template>
    <layout-main>
            <div class="row mb-3">
                <div class="col-6" style="text-align: left">
                    <button v-if="itemsOrder.length > 0 && canUpdate(blocks.content)"
                            class="btn btn-dark btn-lg"
                            @click="reorderItems">
                        <template v-if="!isReordering">
                            <fa-icon icon="save"/> Сохранить порядок
                        </template>
                        <template v-else>
                            <span class="spinner-border"
                                  style="width: 1.5rem; height: 1.5rem;"
                                  role="status"
                                  aria-hidden="true">
                            </span>
                            Порядок сохраняется...
                        </template>
                    </button>
                    <button v-else
                            class="btn btn-light btn-lg"
                            @click="openBadgesEditModal(null)"
                            disabled>
                        <fa-icon icon="check"/> Порядок сохранён
                    </button>
                </div>
                <div v-if="canUpdate(blocks.content)" class="col-6" style="text-align: right">
                    <button class="btn btn-success btn-lg"
                            @click="openBadgesEditModal(null)">
                        Добавить шильдик <fa-icon icon="plus"/>
                    </button>
                </div>
            </div>
        <hr/>
            <div class="row mb-3">
                <div class="col-2 pr-0">
                    <h5>№ П/п</h5>
                </div>
                <div class="col-5">
                    <h5>Текст шильдика</h5>
                </div>
                <div v-if="canUpdate(blocks.content)" class="col-5" style="text-align: center">
                    <h5>Действия</h5>
                </div>
            </div>
        <hr/>
            <draggable v-model="badges"
                       v-bind="dragOptions"
                       style="cursor: move">
            <div class="row mb-2" v-for="(badge, index) in badges">
                <div class="col-2">
                    <h5>{{ index+1 }}.</h5>
                </div>
                <div class="col-5">
                    <h5>
                        <span class="badge badge-dark">
                            {{ badge.text }}
                        </span>
                    </h5>
                </div>
                <div v-if="canUpdate(blocks.content)" class="col-5">
                    <div class="row">
                        <div class="col-6 pr-0" style="text-align: right">
                            <button @click="openBadgesEditModal(index)"
                                    class="btn btn-info btn-md">
                                Изменить <fa-icon icon="pencil-alt"/>
                            </button>
                        </div>
                        <div class="col-6" style="text-align: left">
                            <button @click="deleteBadge(badge.id, index)"
                                    class="btn btn-danger btn-md">
                                Удалить <fa-icon icon="trash-alt"/>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            </draggable>

        <modal-edit-form
                :editing-badge="badgeToEdit"
                @saved="saveBadge"
                modal-name="EditBadgeModal"/>
    </layout-main>
</template>

<script>
    import draggable from 'vuedraggable';
    import Services from "../../../../scripts/services/services";
    import modalMixin from '../../../mixins/modal';
    import Modal from '../../../components/controls/modal/modal.vue';
    import ModalEditForm from "./components/modal-edit-form.vue"

    export default {
        components: {
            draggable,
            Modal,
            ModalEditForm
        },
        mixins: [modalMixin],
        props: {
            iBadges: Array,
            dragOptions: {
                animation: 200,
                sort: true,
            },
        },
        data() {
            return {
                badges: this.iBadges,
                badgeToEdit: {},
                itemsOrder: [],
                isReordering: false,

                replacements: {
                    '\\+': '{plus}',
                    '=': '{equal}'
                }
            };
        },
        methods: {
            /**
             * Добавить новый шильдик или обновить старый
             * @param badge
             */
            saveBadge(badge) {
                Object.entries(this.replacements).forEach(item => {
                    let searchValue = new RegExp(item[0], 'g')
                    badge.text = badge.text.replace(searchValue, item[1])
                });

                // При обновлении старого шильдика //
                if (badge.id) {
                    Services.showLoader();
                    Services.net().put(this.getRoute('productBadges.edit'), badge)
                        .then(data => {
                            this.badges = data.badges;
                            Services.msg("Изменения сохранены");
                        }, () => {
                            Services.msg("Не удалось сохранить изменения",'danger');
                        }).finally(() => {
                        Services.hideLoader();
                    })
                }
                // При создании нового шильдика //
                else {
                    Services.showLoader();
                    Services.net().post(this.getRoute('productBadges.add'), badge)
                        .then(data => {
                            this.badges = data.badges;
                            Services.msg("Изменения сохранены");
                        }, () => {
                            Services.msg("Не удалось сохранить изменения",'danger');
                        }).finally(() => {
                        Services.hideLoader();
                    })
                }
            },
            /**
             * Удалить шильдик
             * @param badgeId
             * @param index
             */
            deleteBadge(badgeId, index) {
                Services.showLoader();
                Services.net().delete(this.getRoute('productBadges.remove'), {
                    id: badgeId
                })
                    .then(() => {
                        this.$delete(this.badges, index);
                        Services.msg("Шильдик успешно удален");
                    }, () => {
                        Services.msg("Не удалось удалить Шильдик",'danger');
                    }).finally(() => {
                    Services.hideLoader();
                })
            },
            // Изменить порядок шильдиков и сохранить на сервере //
            reorderItems() {
                this.isReordering = true;
                Services.net().put(this.getRoute('productBadges.reorder'), {
                    items: JSON.stringify(this.itemsOrder)
                })
                    .then(() => {
                        Services.msg("Новый порядок сохранён");
                        this.itemsOrder = [];
                    }, () => {
                        Services.msg("Не удалось сохранить изменения",'danger');
                    }).finally(() => {
                    this.isReordering = false;
                })
            },
            openBadgesEditModal: async function(index) {
                this.badgeToEdit = index === null ?
                    {} : Object.assign(this.badgeToEdit, this.badges[index]);
                await this.$nextTick();
                this.openModal('EditBadgeModal')
            },
        },
        watch: {
            // Пересортировка шильдиков //
            'badges': {
                handler() {
                    this.itemsOrder = Object.values(this.badges).map((item, index) => ({
                        id: item.id,
                        order_num: index + 1
                        })
                    );
                }
            },
        },
    }
</script>

<style scoped>

</style>