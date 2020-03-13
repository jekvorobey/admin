<template>
    <div class="widget-setting-array"
         v-if="prop.array && prop.array.length">
        <div class="widget-setting-array__item"
             :style="'background-color:' + backgroundColor"
             v-for="(propArrayItem, index) in prop.array"
             :key="prop.code + index"
        >
            <button v-if="canDeleteItems"
                    class="btn btn-danger widget-setting-array__item__btn-remove"
                    type="button"
                    @click="removeItem(index)">
                Удалить
            </button>

            <div class="widget-setting-array__item__block"
                 v-for="(childProp, childPropCode, childPropIndex) in propArrayItem"
                 :key="childProp.code + childPropIndex"
            >
                <label class="widget-setting-array__item__block-label"
                       v-if="!isPrimitive(childProp)"
                       v-show="childProp.isInShownList"
                >
                    {{ childProp.label }}
                </label>

                <vue-tooltip
                        v-if="!isPrimitive(childProp) && childProp.tooltip"
                        v-show="childProp.isInShownList"
                        :text="childProp.tooltip"
                        :link="childProp.tooltip_href"
                ></vue-tooltip>

                <vue-widget-setting-array
                        v-if="childProp.multiple"
                        v-show="childProp.isInShownList"
                        :prop.sync="childProp"
                        :depth-level="depthLevel + 1"
                ></vue-widget-setting-array>

                <vue-widget-setting-complex
                        v-else-if="childProp.type === 'complex'"
                        v-show="childProp.isInShownList"
                        :parent-prop="childProp"
                        :props.sync="childProp.complex"
                        :depth-level="depthLevel + 1"
                ></vue-widget-setting-complex>

                <vue-widget-setting-complex
                        v-else-if="childProp.type === 'widget'"
                        v-show="childProp.isInShownList"
                        :parent-prop="childProp"
                        :props.sync="childProp.widget.props"
                        :depth-level="depthLevel + 1"
                ></vue-widget-setting-complex>

                <vue-widget-setting-primitive
                        v-else
                        v-show="childProp.isInShownList"
                        :prop.sync="childProp"
                ></vue-widget-setting-primitive>
            </div>
        </div>

        <div v-if="canAddItems">
            <button
                    class="btn btn-success widget-setting-array__btn-add"
                    type="button"
                    @click="addItem">
                Добавить
            </button>
            <br />
        </div>
    </div>

    <button class="btn btn-success" v-else
            type="button"
            @click="addItem">
        <i class="fas fa-plus"></i>
    </button>
</template>

<script>
    import {
        fill_prop_recursively_with_default,
        fill_props_is_in_shown_list,
        is_primitive,
    } from "../scripts/widgets-helpers";

    export default {
        inject: ['$validator'],
        props: ['prop', 'depthLevel'],
        data() {
            return {

            };
        },
        computed: {
            backgroundColor() {
                return (this.depthLevel % 2 === 0) ? '#eee' : '#f8fafc';
            },
            canAddItems() {
                if (this.prop.max_count > 0) {
                    return this.prop.array.length < this.prop.max_count;
                }

                return true;
            },
            canDeleteItems() {
                const minCount = this.prop.min_count >= 1 ? this.prop.min_count : 1;
                return this.prop.array.length > minCount
                    || (this.prop.array.length === minCount && minCount === 1 && !this.prop.required);
            },
        },
        watch: {
            'prop.array': {
                handler(newValue, oldValue) {
                    newValue.forEach((propArrayItem) => {
                        fill_props_is_in_shown_list(propArrayItem);
                    });
                },
                deep: true,
            },
        },
        methods: {
            addItem() {
                const newItem = JSON.parse(JSON.stringify(this.prop.array_item));

                for (let k in newItem) {
                    if (newItem.hasOwnProperty(k)) {
                        fill_prop_recursively_with_default(newItem[k]);
                    }
                }

                this.prop.array.push(newItem);
            },
            removeItem(index) {
                this.prop.array.splice(index, 1);
            },
            isPrimitive(childProp) {
                return is_primitive(childProp);
            },
        },
        mounted() {

        }
    }
</script>
