<template>
    <b-modal :id="id" title="Корректировка биллинга" hide-footer ref="modal">
        <template v-slot:default="{close}">
            <div>
                <label>Сумма</label>
                <input class="form-control" v-model="form.value"/>
            </div>
            <div>
                <label>Комментарий</label>
                <textarea class="form-control" v-model="form.comment"></textarea>
            </div>

            <div class="float-right mt-3">
                <b-button @click="close()" variant="outline-primary">Отмена</b-button>
                <button class="btn btn-info" @click="$emit('save', form)">Сохранить</button>
            </div>
        </template>
    </b-modal>
</template>

<script>

export default {
    name: 'modal-bill-correct',
    props: ['id'],
    data() {
        return {
            form: {
                comment: '',
                value: '',
            }
        }
    },
};
</script>

<style scoped>

</style>