<template>
    <b-modal id="modal-delivery-edit" title="Редактирование доставки" hide-footer ref="modal">
        <template v-slot:default="{close}">
            Тут будет форма редактирования доставки

            <div class="float-right mt-3">
                <b-button @click="close()" variant="outline-primary">Отмена</b-button>
                <button class="btn btn-info">Сохранить</button>
            </div>
        </template>
    </b-modal>
</template>

<script>

    export default {
    name: 'modal-delivery-edit',
    props: [
        'model',
    ],
    data() {
        return {
        }
    },
    methods: {
    },
};
</script>