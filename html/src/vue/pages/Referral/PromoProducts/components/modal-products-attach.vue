<template>
    <b-modal id="modal-products-attach" title="Назначить промо-товары реферальным партнерам" hide-footer ref="modal" @show="resetFields()">
        <template v-slot:default="{close}">
            <b-form-group>
                <b-form-checkbox v-model="segments.all">
                    Назначить для всех реф. партнеров
                </b-form-checkbox>
            </b-form-group>

            <div v-if="segments.all !== true">
                <b-form-group>
                    <b>По уровням реф. партнеров:</b>
                    <b-form-checkbox v-for="level in ref_levels"
                                     v-model="segments.levels"
                                     :key="level.id"
                                     :value="level.id">
                        {{level.name}}
                    </b-form-checkbox>
                </b-form-group>

                <b-form-group>
                    <b>По профессиональным предпочтениям:</b>
                    <b-form-checkbox v-model="segments.brand">
                        Для предпочитающих этот бренд
                    </b-form-checkbox>
                    <b-form-checkbox v-model="segments.category">
                        Для предпочитающих эту категорию товаров
                    </b-form-checkbox>
                </b-form-group>

                <b-form-group>
                    <b>По профилю:</b>
                    <b-form-checkbox v-for="activity in activities"
                                     v-model="segments.activities"
                                     :key="activity.id"
                                     :value="activity.id">
                        {{activity.name}}
                    </b-form-checkbox>
                </b-form-group>

                <hr>

                <b>Назначить на отдельных реф. партнеров:</b>
                <v-input placeholder="Введите ID через запятую"
                         v-model="str_user_ids"
                         aria-valuemin="0"/>
            </div>

            <div class="float-right mt-3">
                <b-button @click="close()" variant="outline-danger">Отмена</b-button>
                <button class="btn btn-success" @click="attachPromoProducts">Назначить</button>
            </div>
        </template>
    </b-modal>
</template>

<script>
    import VInput from "../../../../components/controls/VInput/VInput.vue";
    import Services from "../../../../../scripts/services/services";

    export default {
        components: {VInput},
        name: 'modal-products-attach',
        props: [
            'promoProducts',
            'selectedPromos',
            'activities',
            'ref_levels'
        ],
        data() {
            return {
                promo_products: this.promoProducts,
                str_user_ids: '',
                segments_null: {
                    all: false,
                    levels: [],
                    brand: false,
                    category: false,
                    activities: [],
                    user_ids: []
                },
                segments: {
                    all: false,
                    levels: [],
                    brand: false,
                    category: false,
                    activities: [],
                    user_ids: [],
                }
            }
        },
        methods: {
            /**
             * Преобразует строку со списком ID в массив
             * @param ids
             * @returns {*[]|number[]}
             */
            formatIds(ids) {
                if (!ids) {
                    return [];
                }

                return ids
                    .split(',')
                    .map(id => { return parseInt(id); })
                    .filter(id => { return id > 0 });
            },
            attachPromoProducts() {
                Services.showLoader();
                
                // Если ни один критерий не установлен //
                let fieldsEmpty = false;
                if (JSON.stringify(this.segments) === JSON.stringify(this.segments_null))
                    fieldsEmpty = true;

                Services.net().put(this.getRoute('referral.promo-products.attach', {}),
                    {
                        promo_products: JSON.stringify(this.selectedPromos),
                        segments: fieldsEmpty ? null : this.segments
                    }
                ).then(data => {
                    Services.msg('Изменения сохранены');
                    this.$emit('update:promoProducts', data.promoProducts)
                    this.$emit('update:selectedPromos', [])
                }, () => {
                    Services.msg('Не удалось сохранить изменения','danger')
                }).finally(() => {
                    this.$bvModal.hide("modal-products-attach");
                    Services.hideLoader();
                })
            },
            resetFields() {
                this.segments = {
                    all: false,
                    levels: [],
                    brand: false,
                    category: false,
                    activities: [],
                    user_ids: []
                };
                this.str_user_ids = '';
            },
        },
        watch: {
            'str_user_ids': {
                handler(val, oldVal) {
                    if (val && val !== oldVal) {
                        let format = this.formatIds(this.str_user_ids).join(', ');
                        let separator = val.slice(-1) === ','
                            ? ','
                            : (val.slice(-2) === ', ' ? ', ' : '');
                        this.str_user_ids = format + separator;
                        this.segments.user_ids = this.formatIds(this.str_user_ids);
                    }
                },
            },
        },
    };
</script>

<style scoped>

</style>