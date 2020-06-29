<template>
    <b-modal id="productBadgesEdit"
             title="Редактирование шильдиков товара"
             hide-footer ref="modal"
             @show="initiateModal()">
        <template v-slot:default="{close}">
                <b-form-group>
                    <h5>Ярлыки</h5>
                    <b-form-checkbox v-for="badge in only_badges"
                                     v-model="new_badges"
                                     :key="badge.id"
                                     :value="badge.id">
                        {{ badge.text }}
                    </b-form-checkbox>
                </b-form-group>

                <b-form-group>
                    <h5>
                        Скидка
                        <a @click="new_discount = null"
                           class="badge badge-pill badge-light"
                           style="cursor: pointer">
                            очистить
                        </a>
                    </h5>
                    <b-form-radio v-for="discount in only_discounts"
                                  v-model="new_discount"
                                  :value="discount.id"
                                  :key="discount.id">
                        {{ discount.text }}
                    </b-form-radio>
                </b-form-group>

            <div class="float-right mt-3">
                <b-button @click="close()" variant="outline-danger">Отмена</b-button>
                <button @click="saveBadges"class="btn btn-success">Сохранить</button>
            </div>
        </template>
    </b-modal>
</template>

<script>
    import Services from "../../../../../scripts/services/services";

    export default {
        name: "product-badges-modal",
        props: [
            'productId',
            'productBadges',
            'availableBadges'
        ],
        data() {
            return {
                badges: this.availableBadges,
                new_badges: [],
                new_discount: null
            }
        },
        methods: {
            initiateModal() {
                this.new_badges = [];
                this.new_discount = null;
            },
            saveBadges() {
                Services.showLoader();
                this.new_discount ? this.new_badges.push(this.new_discount) : null;

                Services.net().put(this.getRoute('products.attachBadges',{}),
                    {
                        product_ids: this.productId,
                        badges: JSON.stringify(this.new_badges)
                    }
                ).then(() => {
                    Services.msg('Шильдики товара успешно обновлены')
                    this.$emit('save', this.new_badges)
                    this.$bvModal.hide("productBadgesEdit");
                }, () => {
                    Services.msg('Не удалось сохранить изменения', 'danger')
                }).finally(() => {
                    Services.hideLoader();
                })
            }
        },
        computed: {
            only_badges() {
                return Object.values(this.badges).filter(item => {
                    return item.type !== 2
                }).map(item => ({
                    id: item.id,
                    text: item.text
                }))
            },
            only_discounts() {
                return Object.values(this.badges).filter(item => {
                    return item.type === 2
                }).map(item => ({
                    id: item.id,
                    text: item.text
                }))
            },
        },
    }
</script>

<style scoped>

</style>