<template>
    <div>
        <div class="d-flex justify-content-between mt-3 mb-3">
            <button class="btn btn-success" @click="openModal('eventProfessionsModalForm')">Добавить профессию</button>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Название</th>
                    <th class="text-right">Действия</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="publicEventProfession in publicEventProfessions" :key="publicEventProfession.id">
                    <td>{{publicEventProfession.id}}</td>
                    <td>{{publicEventProfession.name}}</td>
                    <td>
                        <v-delete-button @delete="() => onDelete([publicEventProfession.id])" class="float-right ml-1"/>
                    </td>
                </tr>
            </tbody>
        </table>
        <transition name="modal">
            <modal :close="closeModal" v-if="isModalOpen('eventProfessionsModalForm')">
                <div slot="header">
                    Професии
                </div>
                <div slot="body">
                    <profession-form :publicEvent="publicEvent" @onSave="onSave"/>
                </div>
            </modal>
        </transition>
    </div>
</template>

<script>
    import {mapActions} from "vuex";
    import {
        ACT_LOAD_PROFESSIONS,
        ACT_LOAD_EVENT_PROFESSIONS,
        ACT_DELETE_EVENT_PROFESSION,
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
    import ProfessionForm from './forms/profession-form.vue';

    export default {
        mixins: [
            modalMixin,
            validationMixin,
        ],
        components: {
            Modal,
            VInput,
            VDeleteButton,
            ProfessionForm
        },
        props: {
            publicEvent: {},
        },
        data() {
            return {
                publicEventProfessions: []
            };
        },
        methods: {
            ...mapActions(NAMESPACE, {
                loadProfessions: ACT_LOAD_PROFESSIONS,
                loadPublicEventProfessions: ACT_LOAD_EVENT_PROFESSIONS,
                deletePublicEventProfession: ACT_DELETE_EVENT_PROFESSION
            }),
            reload() { 
                this.loadPublicEventProfessions({publicEventId: this.publicEvent.id})
                    .then(data => {
                        this.publicEventProfessions = data.professions;
                    });
            },
            onDelete(ids) {
                Services.showLoader();
                this.deletePublicEventProfession({
                    ids
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
        computed: {
        },
        created() {
            this.reload();
        }
    }
</script>

<style scoped>

</style>