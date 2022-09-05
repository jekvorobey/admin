<template>
    <b-modal id="productBadgesEdit"
             title="Редактирование шильдиков товара"
             hide-footer ref="modal"
             @show="initiateModal()">
        <template v-slot:default="{close}">
                <b-form-group>
                    <h5>Шильдики</h5>
                    <b-form-checkbox v-for="badge in badges"
                                     v-model="productBadges"
                                     :key="badge.id"
                                     :value="badge.id">
                        {{ badge.text }}
                    </b-form-checkbox>
                </b-form-group>

            <div class="float-right mt-3">
                <b-button @click="close()" variant="outline-danger">Отмена</b-button>
                <button @click="saveBadges" class="btn btn-success">Сохранить</button>
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
            'availableBadges',
            'attachedBadges'
        ],
        data() {
            return {
                badges: this.availableBadges,
                productBadges: [],
            }
        },
        methods: {
            initiateModal() {
                this.productBadges = this.attachedBadges ? this.attachedBadges : [];
            },
            saveBadges() {
                Services.showLoader();
                Services.net().put(this.getRoute('products.updateBadges', {}),
                    {
                        product_ids: this.productId,
                        badges: JSON.stringify(this.productBadges)
                    }
                ).then(() => {
                    Services.msg('Шильдики товара успешно обновлены')
                    this.$emit('save', this.productBadges)
                    this.$bvModal.hide("productBadgesEdit");
                }, () => {
                    Services.msg('Не удалось сохранить изменения', 'danger')
                }).finally(() => {
                    Services.hideLoader();
                })
            }
        },
    }
</script>

<style scoped>

</style>