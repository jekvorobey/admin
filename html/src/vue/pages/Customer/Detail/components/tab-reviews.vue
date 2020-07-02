<template>
    <div>
        <table class="table table-sm mt-3">
            <thead>
            <tr>
                <th>Пользователь</th>
                <th>Товар</th>
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
                    <td>
                        <a :href="getRoute('customers.detail', {id: review.user.id})">
                            {{ review.user.full_name }}
                        </a>
                    </td>
                    <td>
                        <a :href="getRoute('products.detail', {id: review.product.id})">
                            {{ review.product.name }}
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
            }
        },
        created() {
            Services.showLoader();
            Services.net().get(
                this.getRoute('customers.detail.reviews', {id: this.id})
            ).then(data => {
                this.reviews = data.reviews;
            }).finally(() => {
                Services.hideLoader();
            })
        }
    };
</script>

<style scoped>

</style>