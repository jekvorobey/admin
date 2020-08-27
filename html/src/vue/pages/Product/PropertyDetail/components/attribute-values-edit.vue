<template>
    <div class="col-6">
        <h4 class="text-muted mb-4">Атрибут может принимать значения:</h4>

        <em class="text-muted">
            Будет готово в рамках задачи по созданию справочника значений для товарных атрибутов
        </em>

        <v-input class="mt-4" placeholder="Значение-заглушка 1" disabled/>
        <v-input placeholder="Значение-заглушка 2" disabled/>
        <v-input placeholder="Значение-заглушка 3" disabled/>
        <button class="btn btn-dark float-right" disabled>
            <fa-icon icon="plus"/> Добавить значение
        </button>
    </div>
</template>

<script>
import VInput from "../../../../components/controls/VInput/VInput.vue";
export default {
    components: {VInput}
}
</script>

<style scoped>

</style>