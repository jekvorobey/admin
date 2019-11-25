<template>
    <div class="shadow mt-3 p-3 w-100">
        <table class="table table-condensed" v-if="shipments">
            <thead>
            <tr>
                <th>#</th>
                <th>Мерчант</th>
                <th>Стоимость</th>
                <th>Создана</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="(item, index) in shipments">
                <td>{{ item.number }}</td>
                <td>ID: {{ item.merchant_id }}</td>
                <td>{{ item.cost }} руб.</td>
                <td>{{ item.created_at }}</td>
                <td>
                    <fa-icon icon="pencil-alt" title="Изменить" class="cursor-pointer mr-3"
                             @click="editShipment(item.id)"
                    />
                    <fa-icon icon="times" title="Удалить" class="cursor-pointer"
                             @click="deleteShipment(item.id)"
                    />
                </td>
            </tr>
            </tbody>
        </table>
        <span v-else>
            Нет истории изменений!
        </span>
    </div>
</template>
<script>
import {mapGetters} from "vuex";

export default {
    data() {
        return {

        }
    },
    props: {
        shipments: {},
    },
    methods: {
        editShipment(id) {

        },
        deleteShipment(id) {

        },
        editProduct(id) {

        },
        deleteProduct(id) {

        }
    },
    computed: {
        ...mapGetters(['getRoute']),
    },
    mounted() {
    }
}
</script>

<style scoped>

</style>
