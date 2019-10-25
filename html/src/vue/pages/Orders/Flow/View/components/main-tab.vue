<template>
    <div class="row mt-3">
        <div class="col-4">
            <div class="shadow p-3 w-100">
                123
            </div>
        </div>
        <div class="col-4">
            <div class="card border-0 shadow w-100">
                <div class="card-header">
                    Комментарий клиента
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <textarea class="form-control" placeholder="Нет комментариев..." rows="3" v-model="customer_comment"></textarea>
                    </div>
                </div>
            </div>
            <div class="card border-0 shadow w-100 mt-3">
                <div class="card-header">
                    Служебные примечания
                </div>
                <div class="card-body">
                    <div class="additions-wrapper overflow-auto" v-if="customer_additions">
                        <p v-for="addition in customer_additions">
                            {{ addition }}
                        </p>
                    </div>
                    <p v-else class="text-center p-3">
                        Нет служебных примечаний
                    </p>
                    <div class="form-group">
                        <textarea class="form-control" placeholder="Нет комментариев..." rows="3" v-model="new_addition"></textarea>
                        <button @click="addAddition()">Добавить</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="shadow p-3 w-100">
                123
            </div>
        </div>
    </div>
</template>

<script>
import {mapGetters} from "vuex";

export default {
    data() {
        return {
            customer_comment: null,
            customer_additions: {},
            new_addition: null,
        }
    },
    props: {
        customer_notes: Array,
        // history: Array,
        // pager: Object
    },
    methods: {
        addAddition() {

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
