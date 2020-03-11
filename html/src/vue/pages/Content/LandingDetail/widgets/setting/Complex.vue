<template>
    <div class="widget-setting-complex"
         :style="'background-color:' + backgroundColor"
         v-if="props && show">
        <button v-if="!required"
                class="btn btn-danger widget-setting-complex__btn-remove"
                type="button"
                @click="removeComplex">
            Удалить
        </button>
        <button class="btn btn-primary widget-setting-complex__btn-hide"
                type="button"
                @click="hideComplex">
            Свернуть
        </button>

        <div class="widget-setting-complex__block" v-for="prop in propsOrFieldsets" :key="prop.code">
            <label class="widget-setting-complex__block-label"
                   v-if="!isPrimitive(prop)"
                   v-show="prop.isInShownList"
            >
                {{ prop.label }}
            </label>

            <vue-tooltip
                    v-if="!isPrimitive(prop) && prop.tooltip"
                    v-show="prop.isInShownList"
                    :text="prop.tooltip"
                    :link="prop.tooltip_href"
            ></vue-tooltip>

            <vue-widget-setting-array
                    v-if="prop.multiple"
                    v-show="prop.isInShownList"
                    :prop.sync="prop"
                    :depth-level="depthLevel + 1"
            ></vue-widget-setting-array>

            <vue-widget-setting-complex
                    v-else-if="prop.type === 'complex'"
                    v-show="prop.isInShownList"
                    :parent-prop="prop"
                    :props.sync="prop.complex"
                    :depth-level="depthLevel + 1"
            ></vue-widget-setting-complex>

            <vue-widget-setting-complex
                    v-else-if="prop.type === 'widget'"
                    v-show="prop.isInShownList"
                    :parent-prop="prop"
                    :props.sync="prop.widget.props"
                    :depth-level="depthLevel + 1"
            ></vue-widget-setting-complex>

            <vue-widget-setting-primitive
                    v-else
                    v-show="prop.isInShownList"
                    :prop.sync="prop"
            ></vue-widget-setting-primitive>
        </div>
    </div>
    <button class="btn btn-success widget-setting-complex__btn-add" v-else-if="props && !show"
            type="button"
            @click="addComplex">
        <i class="fas fa-plus"></i>
    </button>
</template>

<script>
    import {
        fetch_fieldsets_from_props,
        fill_prop_recursively_with_default,
        fill_prop_recursively_with_null,
        fill_props_is_in_shown_list,
        is_primitive,
    } from "../../widgets-helpers";

    export default {
        inject: ['$validator'],
        props: ['props', 'parentProp', 'depthLevel'],
        data() {
            return {
                show: true,
                removed: false,
                propsOrFieldsets: {},
            };
        },
        computed: {
            required() {
                return this.parentProp && this.parentProp.required;
            },
            backgroundColor() {
                return (this.depthLevel % 2 === 0) ? '#eee' : '#f8fafc';
            },
        },
        watch: {
            props: {
                handler(newValue, oldValue) {
                    fill_props_is_in_shown_list(newValue);
                    this.propsOrFieldsets = fetch_fieldsets_from_props(newValue, this.parentProp);
                },
                deep: true,
            },
        },
        methods: {
            removeComplex() {
                this.show = false;
                this.removed = true;

                for (let k in this.props) {
                    if (this.props.hasOwnProperty(k)) {
                        fill_prop_recursively_with_null(this.props[k], k);
                    }
                }
            },
            hideComplex() {
                this.show = false;
            },
            addComplex() {
                if (this.removed) {
                    for (let k in this.props) {
                        if (this.props.hasOwnProperty(k)) {
                            fill_prop_recursively_with_default(this.props[k]);
                        }
                    }
                }

                this.show = true;
                this.removed = false;
            },
            isPrimitive(prop) {
                return is_primitive(prop);
            },
        },
        mounted() {
            this.show = this.required;

            if (this.props && this.parentProp) {
                this.propsOrFieldsets = fetch_fieldsets_from_props(this.props, this.parentProp);
            }
        }
    }
</script>
