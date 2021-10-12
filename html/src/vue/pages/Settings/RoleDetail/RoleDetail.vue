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
                            <tr v-for="blockPermission in blockPermissions">
                                <td>{{blockName(blockPermission.block_id)}}</td>
                                <td>{{permissionName(blockPermission.permission_id)}}</td>
                                <td><fa-icon @click="deleteBlock(blockPermission.id)" icon="trash-alt" class="icon-btn icon-btn--red"></fa-icon></td>
                            </tr>
                        </tbody>
                    </table>
                </shadow-card>
            </div>
        </div>
        <role-edit-modal :source="role" :fronts="options.fronts" @onSave="updateRole"></role-edit-modal>
        <add-role-block-modal :source="role" :blocks="options.blocks" :permissions="options.permissions" @onSave="onBlockCreated"></add-role-block-modal>
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
    import AddRoleBlockModal from '../components/add-role-block-modal.vue';

    export default {
        mixins: [modalMixin],
        components: {
            ShadowCard,
            ValuesTable,
            modal,
            VSelect,
            RoleEditModal,
            AddRoleBlockModal
        },
        props: {
            iRole: {},
            iBlockPermissions: {},
            options: {},
        },
        data() {
            return {
                role: this.iRole,
                blockPermissions: this.iBlockPermissions,
                roleValuesNames: {
                    name: 'Наименование',
                    front: 'Система',
                    created_at: 'Дата добавления',
                    updated_at: 'Дата обновления',
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
                    blockPermissions: this.newBlock,
                })
                    .then(data => {
                        this.newBlock = null;
                        this.blockPermissions = data.blockPermissions;
                        this.showMessageBox({text: 'Блок добавлен'});
                    });
            },
            deleteBlock(id) {
                Services.net().post(this.getRoute('role.deleteBlock', {id: this.role.id}), {}, {
                    block_id: id
                })
                    .then(data => {
                        this.blockPermissions = data.blockPermissions;
                    });
            },
            updateRole(newData) {
                Object.assign(this.role, newData);
                this.closeModal();
            },
            onBlockCreated() {
              this.showMessageBox({text: "Блок с разрешением добавлен!"});
            }
        },
        computed: {
            roleInfo() {
                return {
                    name: this.role.name,
                    front: this.frontName(this.role.front),
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
