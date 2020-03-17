<template>
    <b-modal id="modal-find-product" title="Поиск товара" size="lg">
        <b-form @submit.prevent="findItems()">
            <label for="find-product-name">Название</label>
            <b-input-group>
                <b-form-input id="find-product-name" placeholder="Введите название" v-model="searchString"/>
                <b-input-group-append>
                    <b-button type="submit" variant="dark">Искать</b-button>
                </b-input-group-append>
            </b-input-group>
        </b-form>
        <div class="pb-3">
            <ul>
                <li v-for="item in items">
                    <a :href="'/products/' + item.id">{{ item.name }}</a>
                </li>
            </ul>
        </div>
    </b-modal>
</template>

<script>
    import Services from "../../../../../scripts/services/services";

    export default {
        name: 'modal-find-product',
        data() {
            return {
                searchString: '',
                items: []
            }
        },
        methods: {
            findItems() {
                let filter;
                filter = {'name' : this.searchString};
                let page = 1;
                Services.net().get(this.getRoute('products.listPage'), {page, filter})
                    .then(data => { this.items = data.products });
            }
        }
    }
</script>