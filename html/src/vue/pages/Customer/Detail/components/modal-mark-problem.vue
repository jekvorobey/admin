<template>
    <b-modal id="modal-mark-problem" title="Редактирование портфолио" hide-footer>
        <template v-slot:default="{close}">

            <textarea class="form-control" v-model="form.comment_problem_status"></textarea>

            <div class="float-right mt-3">
                <b-button @click="close()" variant="outline-primary">Отмена</b-button>
                <button class="btn btn-info" @click="saveStatus">Сохранить</button>
            </div>
        </template>
    </b-modal>
</template>

<script>
import Services from '../../../../../scripts/services/services.js';
import { mapGetters } from 'vuex';

export default {
    name: 'modal-mark-problem',
    props: ['model', 'statusProblem'],
    data() {
        return {
            form: {
                comment_problem_status: this.model.comment_problem_status,
            }
        }
    },
    watch: {
        'model.comment_problem_status': function (val, oldVal) {
            this.form.comment_problem_status = this.model.comment_problem_status;
        }
    },
    computed: {
        ...mapGetters(['getRoute']),
        customer: {
            get() {return this.model},
            set(value) {this.$emit('update:model', value)},
        },
    },
    methods: {
        saveStatus() {
            if (!this.form.comment_problem_status) {
                Services.msg("Введите комментарий", 'danger');
                return;
            }
            Services.showLoader();
            Services.net().put(this.getRoute('customers.detail.save', {id: this.model.id}), {}, {
                customer: {
                    status: this.statusProblem,
                    comment_problem_status: this.form.comment_problem_status,
                },
            }).then(data => {
                this.customer.comment_problem_status = this.form.comment_problem_status;
                this.customer.status = this.statusProblem;
                Services.hideLoader();
                this.$bvModal.hide("modal-mark-problem");
            });
        },
    }
};
</script>

<style scoped>

</style>