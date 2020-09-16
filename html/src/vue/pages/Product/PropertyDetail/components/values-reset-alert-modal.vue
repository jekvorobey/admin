<template>
    <b-modal id="modal-values-reset-alert" title="Смена типа товарного атрибута" hide-footer>
        <template v-slot:default="{close}">
            <p>
                Тип товарного атрибута был изменён.
                <br/><br/>
                У всех товаров, использующих данный атрибут,
                будут сброшены его значения.
                <br/><br/>
                Продолжить?
            </p>

            <div class="float-right mt-3">
                <b-button @click="close()" variant="outline-secondary">Отмена</b-button>
                <button class="btn btn-success" @click="proceed">Сохранить изменения</button>
            </div>
        </template>
    </b-modal>
</template>

<script>
export default {
    methods: {
        proceed() {
            this.$emit('proceed');
            this.$bvModal.hide('modal-values-reset-alert');
        }
    }
}
</script>