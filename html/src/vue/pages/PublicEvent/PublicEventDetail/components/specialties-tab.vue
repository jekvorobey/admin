<template>
    <div>
        <div class="d-flex justify-content-between mt-3 mb-3" v-if="canUpdate(blocks.events)">
            <button class="btn btn-success" @click="openModal('eventSpecialtiesModalForm')">Добавить направление</button>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Название</th>
                    <th class="text-right" v-if="canUpdate(blocks.events)">Действия</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="specialty in specialties" :key="specialty.id">
                    <td>{{specialty.id}}</td>
                    <td>{{specialty.name}}</td>
                    <td v-if="canUpdate(blocks.events)">
                        <v-delete-button @delete="() => onDelete(specialty.id)" class="float-right ml-1"/>
                    </td>
                </tr>
            </tbody>
        </table>
        <transition name="modal">
            <modal :close="closeModal" v-if="isModalOpen('eventSpecialtiesModalForm')">
                <div slot="header">
                    Направления
                </div>
                <div slot="body">
                    <specialty-form :publicEvent="publicEvent" :allSpecialtiesList="allSpecialtiesList" @onSave="onSave"/>
                </div>
            </modal>
        </transition>
    </div>
</template>

<script>
    import {mapActions} from "vuex";
    import {
        ACT_LOAD_EVENT_SPECIALTIES,
        ACT_DELETE_EVENT_SPECIALTY,
        NAMESPACE
    } from '../../../../store/modules/public-events';

    import Helpers from '../../../../../scripts/helpers';
    import modalMixin from '../../../../mixins/modal';
    import {validationMixin} from 'vuelidate';
    import {required} from 'vuelidate/lib/validators';

    import Modal from '../../../../components/controls/modal/modal.vue';
    import VInput from '../../../../components/controls/VInput/VInput.vue';
    import VDeleteButton from '../../../../components/controls/VDeleteButton/VDeleteButton.vue';
    import Services from "../../../../../scripts/services/services";
    import SpecialtyForm from './forms/specialty-form.vue';

    export default {
        mixins: [
            modalMixin,
            validationMixin,
        ],
        components: {
            Modal,
            VInput,
            VDeleteButton,
            SpecialtyForm
        },
        props: {
            publicEvent: {},
            allSpecialties: {}
        },
        data() {
            return {
                specialties: [],
                allSpecialtiesList: this.allSpecialties
            };
        },
        methods: {
            ...mapActions(NAMESPACE, {
                loadPublicEventSpecialties: ACT_LOAD_EVENT_SPECIALTIES,
                deletePublicEventSpecialty: ACT_DELETE_EVENT_SPECIALTY
            }),
            reload() {
                this.loadPublicEventSpecialties({publicEventId: this.publicEvent.id})
                    .then(data => {
                        this.specialties = data.specialties;
                        this.allSpecialtiesList = data.allSpecialties;
                    });
            },
            onDelete(id) {
                Services.showLoader();
                this.deletePublicEventSpecialty({
                    publicEventId: this.publicEvent.id,
                    id: id
                }).then(() => {
                    this.reload();
                }).finally(() => {
                    Services.hideLoader();
                });
            },
            onSave() {
                this.closeModal();
                this.$emit('onChange');
                this.reload();
            },
            onCancel() {
                this.closeModal();
            },
        },
        created() {
            this.reload();
        }
    }
</script>

<style scoped>

</style>
