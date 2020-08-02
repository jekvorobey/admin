<template>
    <div class="pt-3">
        <div class="row">
            <div v-if="children.length > 1" @click="toggle" class="col-sm-5">
                <span :style="indent">
                    {{ category.name }}
                    <fa-icon :icon="opened ? 'angle-up' : 'angle-down'"></fa-icon>
                </span>
            </div>
            <div v-else class="col-sm-5">
                <span :style="indent">
                    {{ category.name }}
                </span>
            </div>
            <div class="col-sm-1 d-flex justify-content-center">
                <input type="checkbox" class="mt-1" :disabled="checkboxDisabled" v-model="item.frequent">
            </div>
            <div class="col-sm-2 d-flex justify-content-start">
                <v-input v-model="item.position"
                         type="number"
                         class="w-50 mr-2 mb-2"
                         :error="errorPositionField()"
                />
            </div>
            <div class="col-sm-4">
                <div v-if="item.file_id" class="mb-2">
                    <img :data-src="media.file(item.file_id)" class="lazyload" style="max-width: 75px;"/>
                    <v-delete-button
                            btn-class="btn-danger btn-sm"
                            @delete="() => item.file_id = null"/>
                </div>
                <file-input
                        v-else
                        @uploaded="(data) => item.file_id = data.id"
                        :error="errorIconField()"
                        class="mb-3 w-50"></file-input>
            </div>
        </div>
        <category-tree-item v-show="opened"
                            v-for="(child, index) in children"
                            :key="child.id"
                            :category="child"
                            :collection="collection"
                            :depth="depth + 1"
                            :selectable="selectable"
                            @onEdit="emit">
        </category-tree-item>
    </div>


</template>

<script>
    import Services from "../../../../../scripts/services/services";
    import VInput from '../../../../components/controls/VInput/VInput.vue';
    import FileInput from "../../../../components/controls/FileInput/FileInput.vue";
    import VDeleteButton from "../../../../components/controls/VDeleteButton/VDeleteButton.vue";
    import { validationMixin } from 'vuelidate';
    import { required, requiredIf, integer } from 'vuelidate/lib/validators';

    export default {
        name: "category-tree-item",
        components: {
            VInput,
            FileInput,
            VDeleteButton,
        },
        mixins: [validationMixin],
        props: {
            category: Object,
            collection: Array,
            depth: Number,
            selectable: Boolean
        },
        data() {
            return {
                opened: false,
                frequent: false,
                item: {
                    id: this.category.id,
                    frequent: this.category.frequent,
                    position: this.category.position,
                    file_id: this.category.file_id,
                }
            }
        },
        validations() {
            return {
                item: {
                    position: {
                        integer,
                        required,
                    },
                    file_id: {
                        integer,
                        required: requiredIf(function () {
                            return this.item.frequent;
                        }),
                    },
                },
            }
        },
        methods: {
            toggle() {
                this.opened = !this.opened;
            },
            emit(value) {
                this.$emit('onEdit', value);
            },
            errorPositionField() {
                if (this.$v.item.position.$invalid) {
                    return "Укажите любое значение";
                }
            },
            errorIconField() {
                if (this.$v.item.file_id.$invalid) {
                    return "Прикрепите иконку в формате svg";
                }
            },
        },
        computed: {
            indent() {
                return {
                    'margin-left': `${40 * this.depth}px`,
                };
            },
            children() {
                return this.collection.filter(category => {
                    return category.parent_id === this.category.id;
                });
            },
            checkboxDisabled() {
                return !this.item.frequent && !this.selectable;
            },
        },
        watch: {
            'item': {
                handler(value) {
                    this.emit({
                        'item': value,
                        'invalid': this.$v.item.$invalid,
                    });
                },
                deep: true
            }
        }
    }
</script>

<style scoped>
    .row {
        margin-left: 0;
        margin-right: 0;
        border-bottom: 1px solid #dee2e6;
    }
</style>