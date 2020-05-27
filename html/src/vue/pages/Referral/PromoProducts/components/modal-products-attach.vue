<template>
    <b-modal id="modal-products-attach" title="Назначить промо-товары реферальным партнерам" hide-footer ref="modal">
        <template v-slot:default="{close}">
            json: {{segments}} <hr>
            <b-form-group>
                <b-form-checkbox v-model="segments.all">
                    Назначить для всех реф. партнеров
                </b-form-checkbox>
            </b-form-group>

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
                     v-model="segments.ids"
                     aria-valuemin="0"/>

            <div class="float-right mt-3">
                <b-button @click="close()" variant="outline-danger">Отмена</b-button>
                <button class="btn btn-success">Назначить</button>
            </div>
        </template>
    </b-modal>
</template>

<script>
    import VInput from "../../../../components/controls/VInput/VInput.vue";

    export default {
        components: {VInput},
        name: 'modal-products-attach',
        props: [
            'promoIds',
            'activities',
            'ref_levels'
        ],
        data() {
            return {
                segments: {
                    all: false,
                    levels: [],
                    brand: false,
                    category: false,
                    activities: [],
                    ids: ''
                }
            }
        },
        methods: {

        },
    };
</script>

<style scoped>

</style>