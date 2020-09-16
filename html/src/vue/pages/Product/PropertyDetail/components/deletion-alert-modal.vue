<template>
    <b-modal id="modal-deletion-alert" size="lg" title="Удаление товарного атрибута" hide-footer>
        <template v-slot:default="{close}">
            <p class="text-danger">Удаление товарного атрибута будет иметь следующие последствия:</p>
            <ul>
                <li>Атрибут исчезнет из Шаблонов для импорта товаров</li>
                <li>Атрибут вместе со значением исчезнет у всех товаров, использующих его</li>
                <li>Атрибут исчезнет из списка фильтров в публичной части сайта</li>
                <li>Будут удалены Товарные группы, использующие данный атрибут</li>
                <li v-if="type === 'directory'">
                    Все возможные значения из списка для атрибута также будут удалены навсегда
                </li>
            </ul>

            <div class="float-right mt-3">
                <b-button @click="close()" variant="outline-secondary" :disabled="inProgress">Отмена</b-button>
                <button class="btn btn-danger" @click="proceed" :disabled="inProgress">Удалить атрибут</button>
            </div>
        </template>
    </b-modal>
</template>

<script>
import Services from "../../../../../scripts/services/services.js";
export default {
    props: ['type', 'propId'],
    data() {
        return {
            inProgress: false
        }
    },
    methods: {
        proceed() {
            this.inProgress = true;
            Services.showLoader();
            Services.net().delete(this.getRoute('products.properties.delete', {id: this.propId})
            ).then(() => {
                Services.msg('Товарный атрибут успешно удален, перенаправление на список товарных атрибутов...')
                setTimeout(() => {
                    window.location = this.getRoute(
                        'products.properties.list', {}
                    )}, 1000);
            }, () => {
                this.inProgress = false;
                Services.msg('Возникла ошибка при удалении товарного атрибута','danger')
            }).finally(() => {
                Services.hideLoader();
            })
        }
    }
}
</script>

<style scoped>

</style>