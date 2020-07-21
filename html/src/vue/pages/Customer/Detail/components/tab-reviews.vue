<template>
    <div>
        <table class="table table-sm mt-3">
            <thead>
            <tr>
                <th>Отзыв на</th>
                <th>Объект отзыва</th>
                <th>Рейтинг</th>
                <th>Сообщение</th>
                <th>Достоинства</th>
                <th>Недостатки</th>
                <th>Файлы</th>
                <th>Лайки/дизлайки</th>
                <th>Создан</th>
            </tr>
            </thead>
            <tbody>
            <template v-if="reviews.length > 0">
                <tr v-for="review in reviews">
                    <td>{{ review.object_type === 'product' ? 'Товар' : 'Мастер-класс' }}</td>
                    <td>
                        <a v-if="review.object_type === 'product'" :href="getRoute('products.detail', {id: review.object.id})">
                            {{ review.object.name }}
                        </a>
                        <a v-else :href="getRoute('public-event.detail', {event_id: review.object.id})">
                            {{ review.object.name }}
                        </a>
                    </td>
                    <td>{{ review.rating }}</td>
                    <td>{{ review.body }}</td>
                    <td>{{ review.pros }}</td>
                    <td>{{ review.cons }}</td>
                    <td>
                        <div v-for="file in review.files">
                            <a :href="file.url" target="_blank">{{ file.name }}</a>
                        </div>
                    </td>
                    <td>{{ review.likes}}/{{review.dislikes }}</td>
                    <td>{{ review.created_at }}</td>
                </tr>
            </template>
            <template v-else>
                <tr>
                    <td class="text-center" colspan="8">
                        <div class="bg-light p-5">
                            Список отзывов пуст.
                        </div>
                    </td>
                </tr>
            </template>
            </tbody>
        </table>
        <b-pagination
                v-if="total > perPage"
                v-model="currentPage"
                :total-rows="total"
                :per-page="perPage"
        ></b-pagination>
    </div>
</template>

<script>
    import Services from '../../../../../scripts/services/services.js';

    export default {
        name: 'tab-reviews',
        props: ['id'],
        data() {
            return {
                reviews: [],
                currentPage: 1,
                perPage: null,
                total: null,
            }
        },
        created() {
            Services.showLoader();
            Promise.all([
                Services.net().get(this.getRoute('customers.detail.reviews.data', {id: this.id})),
                this.paginationPromise()
            ]).then(data => {
                this.perPage = data[0].perPage;
                this.reviews = data[1].reviews;
                this.total = data[1].total;
            }).finally(() => {
                Services.hideLoader();
            })
        },
        methods: {
            paginationPromise() {
                return Services.net().get(
                    this.getRoute(
                        'customers.detail.reviews.pagination',
                        {id: this.id}
                    ),
                    {
                        page: this.currentPage,
                    });
            },
            loadPage() {
                this.processing = true;
                Services.showLoader();
                this.paginationPromise().then(data => {
                    this.reviews = data.reviews;
                    this.total = data.total;
                }).finally(() => {
                    Services.hideLoader();
                });
            },
        },
        watch: {
            currentPage() {
                this.loadPage();
            }
        },
    };
</script>

<style scoped>

</style>