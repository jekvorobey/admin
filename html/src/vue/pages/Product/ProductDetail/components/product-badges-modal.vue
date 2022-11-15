<template>
    <b-modal id="productBadgesEdit"
             title="Редактирование шильдиков товара"
             hide-footer ref="modal"
             @show="initiateModal()"
    >
        <template v-slot:default="{close}">

            <!--  Badges with date -->
            <b-form-group v-if="onAdd || onUpdate">
                <h5>Шильдики</h5>
                <b-form-checkbox v-for="badge in productBadgesWithDate"
                                 v-model="productBadges"
                                 :key="badge.id"
                                 :value="badge"
                >
                    <span>{{ badge.text }}</span>
                    <transition name="fade">
                        <div v-if="checkIncludesID(badge.id)" class="date-row">
                            <b class="mr-2">с:</b>
                            <f-date class="mr-3" v-model="badge.start_at"></f-date>
                            <b class="mr-2">по:</b>
                            <f-date v-model="badge.end_at"></f-date>
                        </div>
                    </transition>
                </b-form-checkbox>
            </b-form-group>
            <!--  Badges with date -->

            <!--  Badges WITHOUT date -->
            <b-form-group v-else>
                <h5>Шильдики</h5>
                <b-form-checkbox v-for="badge in badges"
                                 v-model="productBadges"
                                 :key="badge.id"
                                 :value="badge.id"
                >
                    <span>{{ badge.text }}</span>
                </b-form-checkbox>
            </b-form-group>
            <!--  Badges WITHOUT date -->

            <div class="float-right mt-3">
                <b-button @click="close()" variant="outline-danger">Отмена</b-button>
                <button v-if="onUpdate" @click="updateBadges" class="btn btn-success">Обновить</button>
                <button v-if="onAdd" @click="addBadges" class="btn btn-success">Добавить</button>
                <button v-if="onDelete" @click="deleteBadges" class="btn btn-success">Удалить</button>
            </div>
        </template>
    </b-modal>
</template>

<script>
    import Services from "../../../../../scripts/services/services";
    import FDate from '../../../../components/filter/f-date.vue';

    export default {
        name: "product-badges-modal",
        components: {FDate},
        props: [
            'productId',
            'availableBadges',
            'attachedBadges',
            'badgesAction'
        ],
        data() {
            return {
                badges: this.availableBadges,
                productBadges: [],
                productBadgesWithDate: []
            }
        },
        computed: {
            onUpdate() {
                return (this.badgesAction === null || undefined) || !this.badgesAction
            },
            onAdd() {
                return this.badgesAction === 'add'
            },
            onDelete() {
                return this.badgesAction === 'delete'
            }
        },
        methods: {
            initiateModal() {
                this.productBadges = this.attachedBadges ? this.attachedBadges : [];

                let checkedArray = this.productBadges;
                this.productBadges = [];
                this.productBadgesWithDate = [];

                if (Array.isArray(this.badges)) {
                    this.badges.forEach(item => {
                        let obj = {
                            id: item.id,
                            text: item.text,
                            'start_at': null,
                            'end_at': null,
                        }
                        this.productBadgesWithDate.push(obj)
                    })
                } else {
                    for (let field in this.badges) {
                        let obj = {
                            id: this.badges[field].id,
                            text: this.badges[field].text,
                            'start_at': null,
                            'end_at': null,
                        }
                        this.productBadgesWithDate.push(obj)

                        if (checkedArray.includes(this.badges[field].id)) {
                            this.productBadges.push(obj)
                        }
                    }
                }
            },
            updateBadges() {
                console.log('обновление шильдиков')
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
            },
            addBadges() {
                Services.showLoader();
                Services.net().put(this.getRoute('products.attachBadges', {}),
                    {
                        product_ids: this.productId,
                        badges: JSON.stringify(this.productBadges)
                    }
                ).then(() => {
                    Services.msg('Шильдики товара успешно добавлены')
                    this.$emit('save', this.productBadges)
                    this.$bvModal.hide("productBadgesEdit");
                }, () => {
                    Services.msg('Не удалось сохранить изменения', 'danger')
                }).finally(() => {
                    Services.hideLoader();
                })
            },
            deleteBadges() {
                console.log('удаление шильдиков')
                Services.showLoader();
                Services.net().put(this.getRoute('products.detachBadges', {}),
                    {
                        product_ids: this.productId,
                        badges: JSON.stringify(this.productBadges)
                    }
                ).then(() => {
                    Services.msg('Шильдики товара успешно удалены')
                    this.$emit('save', this.productBadges)
                    this.$bvModal.hide("productBadgesEdit");
                }, () => {
                    Services.msg('Не удалось сохранить изменения', 'danger')
                }).finally(() => {
                    Services.hideLoader();
                })
            },
            checkIncludesID(id) {
                for (let field in this.productBadges) {
                    if (id === this.productBadges[field].id) {
                        return true
                    }
                }
                return false
            }
        },
    }
</script>

<style scoped>
    .date-row {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
    }

    .fade-enter-active, .fade-leave-active {
        transition: opacity .5s
    }

    .fade-enter, .fade-leave-to {
        opacity: 0
    }
</style>