<template>
    <div>
        <div>
            <div class="row mb-3">
                <div class="col-12 mt-3">
                    <a class="btn btn-success" :href="createLink">Создать дизайн</a>
                </div>
            </div>

            <table class="table table-condensed">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Название дизайна</th>
                    <th>Превью дизайна</th>
                    <th>Статус</th>
                    <th>Дата добавления</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <row-design
                    v-for="item in items"
                    :key="'d' + item.id"
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
    </div>
</template>

<script>
import RowDesign from './row-design.vue'
import TabList from '../mixins/TabList.js'

export default {
    components: {RowDesign},
    mixins: [TabList],
    data() {
        return {
            tabName: 'designs'
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
            return this.getRoute('certificate.designs_add')
        }
    },
}
</script>

<style>
.gift-cards-design-list-preview {
    width: 100px;
    height: 50px;
    background-color: #eee;
    background-size: cover;
    background-repeat: no-repeat;
    border: 1px solid #eee;
    border-radius: 6px;
    display: block;
}
</style>
