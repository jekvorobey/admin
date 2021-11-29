<template>
    <layout-main>
        <b-row class="mb-2" v-if="canUpdate(blocks.events)">
            <b-col>
                <button class="btn btn-success btn-sm" @click="openSpecialty()">
                    <fa-icon icon="plus"/>
                </button>
            </b-col>
        </b-row>
        <table class="table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Название</th>
                <th>Активность</th>
                <th v-if="canUpdate(blocks.events)"></th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="specialty in specialties">
                <td>{{ specialty.id }}</td>
                <td>{{ specialty.name }}</td>
                <td>{{ specialty.active ? 'Да' : 'Нет' }}</td>
                <td v-if="canUpdate(blocks.events)">
                    <button class="btn btn-warning btn-sm" @click="openSpecialty(specialty)">
                        <fa-icon icon="edit"/>
                    </button>
                    <button class="btn btn-danger btn-sm" @click="deleteSpecialty(specialty.id)">
                        <fa-icon icon="trash-alt"/>
                    </button>
                </td>
            </tr>
            </tbody>
        </table>
        <div>
            <b-pagination
                v-if="pager.pages > 1"
                v-model="currentPage"
                :total-rows="pager.total"
                :per-page="pager.pageSize"
                @change="changePage"
                :hide-goto-end-buttons="pager.pages < 20"
                class="mt-3 float-right"
            ></b-pagination>
        </div>
        <form-modal modal-name="FormSpecialty" @accept="saveSpecialty" :model.sync="specialty"/>
    </layout-main>
</template>

<script>

import FormModal from './components/form-modal.vue';
import modalMixin from '../../../mixins/modal';
import Services from '../../../../scripts/services/services.js';
import withQuery from "with-query";

export default {
    mixins: [modalMixin],
    props: {
        iSpecialties: {},
        iPager: {},
        iCurrentPage: {},
    },
    components: {FormModal},
    data() {
        return {
            specialties: this.iSpecialties,
            specialty: {},
            pager: this.iPager,
            currentPage: this.iCurrentPage,
        };
    },
    methods: {
        changePage(newPage) {
            history.pushState(null, null, location.origin + location.pathname + withQuery('', {
                page: newPage,
            }));
        },
        loadPage() {
            Services.showLoader();
            Services.net().get(this.route('public-event.specialties.page'), {
                page: this.currentPage,
            }).then(data => {
                this.specialties = data.items;
                if (data.pager) {
                    this.pager = data.pager
                }
            }).finally(() => {
                Services.hideLoader();
            });
        },
        fillSpecialty(specialty) {
            return {
                id: specialty ? specialty.id : null,
                name: specialty ? specialty.name : '',
                active: specialty ? specialty.active : 1,
            }
        },
        openSpecialty(specialty) {
            this.specialty = this.fillSpecialty(specialty);
            this.openModal('FormSpecialty');
        },
        saveSpecialty() {
            Services.net().post(this.getRoute('public-event.specialties.save'), {}, this.specialty).then((data) => {
                this.specialties = data.specialties;
                this.closeModal('FormSpecialty');
                this.$bvToast.toast(`Направление ${this.specialty.name} сохранено`, {
                    title: 'Успех',
                    variant: 'success',
                });
                this.status = null;
            });
        },
        deleteSpecialty(id) {
            Services.net().post(this.getRoute('public-event.specialties.delete'), {}, {id: id}, {})
                .then(data => {
                    this.specialties = data.specialties;
                    this.showMessageBox({text: 'Направление удалено'});
                });
        },
    },
    watch: {
        currentPage() {
            this.loadPage();
        }
    },
};
</script>
