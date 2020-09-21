<template>
    <div>
        <b-row class="d-flex justify-content-between mt-3 mb-3">
            <b-col class="col-md-3">
                <v-select2 v-model.number="newPropertyId" class="form-control form-control-sm">
                    <option v-for="property in allProperties" :value="property.id">{{ property.name }}</option>
                </v-select2>
            </b-col>
            <b-col>
                <button
                        class="btn btn-success"
                        @click="addProperty"
                >
                    <fa-icon icon="plus"></fa-icon> Добавить характеристику
                </button>
            </b-col>
        </b-row>
        <b-card v-for="property in usedProperties" :key="property.id" class="mb-4">
            <b-row>
                <div class="col-sm-6">
                    <h4 class="card-title">
                        <fa-icon icon="list-ul"></fa-icon> {{property.name}} (ID: {{property.id}})
                    </h4>
                </div>
                <div class="col-sm-6">
                    <div class="float-right">
                        <v-delete-button @delete="deleteProperties([property.id])" btn-class="btn-danger"/>
                    </div>
                </div>
            </b-row>
            <b-row>
                <div class="col-sm-12">
                    <p><span class="font-weight-bold">Используемые варианты значений:</span></p>
                    <ul :style="property.isColor ? 'list-style-type: none;' : ''">
                        <li v-for="usedValue in property.usedValues">
                            {{usedValue.name}}
                            <template v-if="usedValue.property_directory_value_id">
                                (ID: {{usedValue.property_directory_value_id}})
                            </template>
                            <div v-if="usedValue.color" :style="'background-color:#' + usedValue.color" class="property-color"></div>
                        </li>
                        <li v-if="!property.usedValues.length">Нет вариантов</li>
                    </ul>
                </div>
            </b-row>
        </b-card>
    </div>
</template>
<script>
    import Services from '../../../../../../scripts/services/services';
    import VSelect2 from '../../../../../components/controls/VSelect2/v-select2.vue';
    import VDeleteButton from '../../../../../components/controls/VDeleteButton/VDeleteButton.vue';

    export default {
        props: {
            model: {},
        },
        components: {
            VSelect2,
            VDeleteButton,
        },
        data() {
            return {
                allProperties: [],
                usedProperties: [],
                newPropertyId: 0,
            }
        },
        methods: {
            setData(data) {
                this.variantGroup.id = data.variantGroup.id;
                this.variantGroup.updated_at = data.variantGroup.updated_at;
                this.variantGroup.properties_count = data.variantGroup.properties_count;
                this.allProperties = data.allProperties;
                this.usedProperties = data.usedProperties;
            },
            addProperty() {
                if (!this.newPropertyId) {
                    Services.msg("Выберите характеристику", "danger");
                    return;
                }

                Services.showLoader();
                Services.net().post(
                    this.getRoute('variantGroups.detail.properties.add', {id: this.variantGroup.id, propertyId: this.newPropertyId})
                ).then((data) => {
                    this.setData(data);
                    this.newPropertyId = 0;
                    Services.msg("Добавление характеристики прошло успешно");
                }, () => {
                    Services.msg("Ошибка при добавлении характеристики", "danger");
                }).finally(() => {
                    Services.hideLoader();
                });
            },
            deleteProperties(propertyIds) {
                Services.showLoader();
                Services.net().delete(this.getRoute('variantGroups.detail.properties.delete', {id: this.variantGroup.id}), {
                    propertyIds: propertyIds,
                }).then((data) => {
                    this.setData(data);
                    Services.msg("Удаление характеристики(к) прошло успешно");
                }, () => {
                    Services.msg("Ошибка при удалении характеристики(к)", "danger");
                }).finally(() => {
                    Services.hideLoader();
                });
            },
        },
        computed: {
            variantGroup: {
                get() {return this.model},
                set(value) {this.$emit('update:model', value)},
            },
        },
        created() {
            Services.showLoader();
            Services.net().get(this.getRoute('variantGroups.detail.properties.load', {id: this.model.id})).then(data => {
                this.setData(data);
            }).finally(() => {
                Services.hideLoader();
            });
        }
    }
</script>
<style>
    .property-color {
        border: 1px solid #000;
        border-radius: 10px;
        width: 20px;
        height: 20px;
        float: left;
        margin-right: 10px;
    }
</style>
