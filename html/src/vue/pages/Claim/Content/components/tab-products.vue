<template>
    <div>
        <table class="table table-condensed">
            <thead>
            <tr>
                <th>ID товара</th>
                <th>Название</th>
                <th>Артикул</th>
                <th>Бренд</th>
                <th>Категория</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="item in products">
                <td>{{ item.id }}</td>
                <td>
                    <a :href="getRoute('products.detail', {id: item.id})">
                        {{ item.name }}
                    </a>
                </td>
                <td>{{ item.vendor_code ? item.vendor_code : 'N/A' }}</td>
                <td>{{ item.brand ? item.brand.name : 'N/A' }}</td>
                <td>{{ item.category ? item.category.name : 'N/A' }}</td>
            </tr>
            </tbody>
        </table>
    </div>
</template>

<script>

    export default {
        props: {
            products: Array
        }
    }
</script>