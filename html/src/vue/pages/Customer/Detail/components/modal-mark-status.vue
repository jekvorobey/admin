<template>
    <b-modal :id="id" :title="`Пометить статусом '${customerStatusName[status]}'`" hide-footer>
        <template v-slot:default="{close}">

            <textarea class="form-control" v-model="form.comment_status"></textarea>

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
    name: 'modal-mark-status',
    props: ['model', 'status', 'id'],
    data() {
        return {
            form: {
                comment_status: this.model.comment_status,
            }
        }
    },
    watch: {
        'model.comment_status': function (val, oldVal) {
            this.form.comment_status = this.model.comment_status;
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
            if (!this.form.comment_status) {
                Services.msg("Введите комментарий", 'danger');
                return;
            }
            Services.showLoader();
            Services.net().put(this.getRoute('customers.detail.save', {id: this.model.id}), {}, {
                customer: {
                    status: this.status,
                    comment_status: this.form.comment_status,
                },
            }).then(data => {
                this.customer.comment_status = this.form.comment_status;
                this.customer.status = this.status;
                Services.hideLoader();
                this.$bvModal.hide(this.id);
                Services.msg("Изменения сохранены");
            });
        },
    }
};
</script>

<style scoped>

</style>