<template>
    <b-modal id="modal-find-product" title="Поиск товара" size="lg" ok-only>
        <b-form @submit.prevent="findItems()">
            <label for="find-product-name">Название</label>
            <b-input-group>
                <b-form-input id="find-product-name" placeholder="Введите название" v-model="searchString"/>
                <b-input-group-append>
                    <b-button type="submit" variant="dark">Искать</b-button>
                </b-input-group-append>
            </b-input-group>
        </b-form>
        <div class="pt-3">
            <ul>
                <li v-for="item in items">
                    <a :href="'/products/' + item.id">{{ item.name }}</a>
                    <span @click="addToFavorites(id, item.id, item.name)">
                        <fa-icon icon="plus"/>
                    </span>
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
                items: [],
            }
        },
        props: ['id'],
        methods: {
            findItems() {
                let filter;
                filter = {'name' : this.searchString};
                let page = 1;
                Services.net().get(this.getRoute('products.listPage'), {page, filter})
                    .then(data => { this.items = data.products });
            },
            addToFavorites(id, product_Id, name) {
                Services.net().post(this.getRoute('customers.detail.preference.favorite.add', {id: id,
                    product_id: product_Id})).then(data => {
                    this.$bvModal.hide("modal-find-product");
                    Services.msg(name + "добавлено в избранное пользователя");
                    location.reload();
                });
            },
        }
    }
</script>
