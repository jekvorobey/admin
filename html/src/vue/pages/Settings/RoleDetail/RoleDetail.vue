<template>
    <layout-main>
        <div class="d-flex justify-content-start align-items-start">
            <div class="d-flex flex-column">
                <shadow-card title="Основная информация" :buttons="{onEdit: 'pencil-alt'}" @onEdit="openModal('roleAdd')">
                    <values-table :values="roleInfo" :names="roleValuesNames"/>
                </shadow-card>
                <shadow-card title="Блоки роли" padding="3" :buttons="{onAdd: 'plus'}" @onAdd="openModal('add-role-block')">
                    <table class="table table-sm">
                        <thead>
                        <tr>
                            <th>Блок</th>
                            <th>Разрешение</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                            <tr v-for="block in blocks">
                                <td>{{blockName(block.id)}}</td>
                                <td v-for="permission in permissions">{{permissionName(permission.id)}}</td>
                                <td><fa-icon v-if="block.id !== 2" @click="deleteBlock(block.id)" icon="trash-alt" class="icon-btn icon-btn--red"></fa-icon></td>
                            </tr>
                        </tbody>
                    </table>
                </shadow-card>
            </div>
        </div>
        <transition name="modal">
            <modal :close="closeModal" v-if="isModalOpen('add-role-block')">
                <div slot="header">
                    Добавление блока
                </div>
                <div slot="body">
                    <v-select v-model="newBlock" :options="blockOptions"></v-select>
                    <button @click="addBlock" class="btn btn-dark">Сохранить</button>
                </div>
            </modal>
        </transition>
        <role-edit-modal :source="role" :fronts="options.fronts" @onSave="updateRole"></role-edit-modal>
    </layout-main>
</template>

<script>

    import Services from '../../../../scripts/services/services';
    import modalMixin from '../../../mixins/modal.js';

    import ShadowCard from '../../../components/shadow-card.vue';
    import ValuesTable from '../../../components/values-table.vue';

    import modal from '../../../components/controls/modal/modal.vue';
    import VSelect from '../../../components/controls/VSelect/VSelect.vue';

    import RoleEditModal from '../components/role-add-modal.vue';

    export default {
        mixins: [modalMixin],
        components: {
            ShadowCard,
            ValuesTable,
            modal,
            VSelect,
            RoleEditModal
        },
        props: {
            iRole: {},
            options: {},
        },
        data() {
            return {
                role: this.iRole,
                roleValuesNames: {
                    name: 'Наименование',
                    front: 'Система',
                },
                newBlock: ''
            };
        },
        methods: {
            frontName(id) {
                let fronts = Object.values(this.options.fronts).filter(front => front.id === id);
                return fronts.length > 0 ? fronts[0].name : 'N/A';
            },
            blockName(id) {
                return this.options.blocks.hasOwnProperty(id) ? this.options.blocks[id] : 'N/A';
            },
            permissionName(id) {
                return this.options.permissions.hasOwnProperty(id) ? this.options.permissions[id] : 'N/A';
            },
            addBlock() {
                Services.net().post(this.getRoute('role.addBlock', {id: this.role.id}), {}, {
                    block: this.newBlock
                })
                    .then(data => {
                        this.newBlock = null;
                        this.blocks = data.blocks;
                        this.showMessageBox({text: 'Роль добавлена'});
                    });
            },
            deleteBlock(id) {
                Services.net().post(this.getRoute('role.deleteBlock', {id: this.role.id}), {}, {
                    block: id
                })
                    .then(data => {
                        this.blocks = data.blocks;
                    });
            },
            updateRole(newData) {
                Object.assign(this.role, newData);
                this.closeModal();
            }
        },
        computed: {
            roleInfo() {
                return {
                    name: this.role.id,
                    login: this.role.login,
                    created_at: this.role.created_at,
                    updated_at: this.role.updated_at
                };
            },
            blockOptions() {
                const blockIds = this.blocks.map(block => block.id);
                return this.options.blocks
                    .map(block => ({value: block.id, text: block.name}))
                    .filter(item => blockIds.indexOf(item.value) === -1 && item.value !== 1);
            }
        }
    };
</script>

<style scoped>
    .icon-btn {
        cursor: pointer;
        transition: 0.3s all;
    }
    .icon-btn--red {
        color:var(--red);
    }
    .icon-btn--red:hover {
        color:var(--pink);
    }
</style>
