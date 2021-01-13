<template>

    <div>
        <div class="row mb-3">
            <div class="col-12 mt-3">
                <a class="btn btn-success" :href="createLink">Создать номинал</a>
            </div>
        </div>

        <table class="table table-condensed">
            <thead>
            <tr>
                <th>id</th>
                <th>Номинал (руб)</th>
                <th>Статус</th>
                <th>Период активации (дни)</th>
                <th>Доступные дизайны</th>
                <th>Доступно (шт)</th>
                <th></th>
            </tr>
            </thead>

            <tbody>
            <row-nominal
                v-for="item in items"
                :key="'n' + item.id"
                :item="item"
                @deleted="onDelete"
            />
            </tbody>
        </table>

        <b-pagination
            v-if="pager.pages > 1"
            v-model="currentPage"
            :total-rows="pager.total"
            :per-page="pager.pageSize"
            @change="loadPage"
            :hide-goto-end-buttons="pager.pages < 10"
            class="float-right"
        ></b-pagination>
    </div>
</template>

<script>
import RowNominal from './row-nominal.vue'
import TabList from '../mixins/TabList.js'

export default {
    components: {RowNominal},
    mixins: [TabList],
    data() {
        return {
            tabName: 'nominals'
        };
    },
    methods: {
        onDelete() {
            this.loadPage().then(() => {
                // есть записи - нечего фиксать
                if (this.records.items.length > 0)
                    return;

                const loadedPage = this.records.page
                const lastPage = this.records.pager.pages

                if (loadedPage > lastPage && lastPage > 0) {
                    this.loadPage(lastPage)
                }
            })
        },
    },
    computed: {
        createLink() {
            return this.getRoute('certificate.nominals_add')
        },
    },
}
</script>
