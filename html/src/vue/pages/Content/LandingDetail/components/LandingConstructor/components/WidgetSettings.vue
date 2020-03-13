<template>
    <div class="row widget-settings" v-if="widget">
        <button type="button" class="btn" @click="closeSettings" style="float: right">
            <span aria-hidden="true">×</span>
        </button>

        <h3 class="mb-4">{{ widget.name }}</h3>
        <hr class="my-4">

        <div class="widget-settings__block" v-for="prop in propsOrFieldsets" :key="widget.contentId + '_' + prop.code">
            <label class="widget-settings__block-label"
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

            <widget-setting-array
                    v-if="prop.multiple"
                    v-show="prop.isInShownList"
                    :prop.sync="prop"
                    :depth-level="1"
            ></widget-setting-array>

            <widget-setting-mapping
                    v-else-if="prop.type === 'mapping'"
                    v-show="prop.isInShownList"
                    :image.sync="widget.props[prop.image]"
                    :points.sync="widget.props[prop.points]"
            ></widget-setting-mapping>

            <widget-setting-complex
                    v-else-if="prop.type === 'complex'"
                    v-show="prop.isInShownList"
                    :parent-prop="prop"
                    :props.sync="prop.complex"
                    :depth-level="1"
            ></widget-setting-complex>

            <widget-setting-complex
                    v-else-if="prop.type === 'widget'"
                    v-show="prop.isInShownList"
                    :parent-prop="prop"
                    :props.sync="prop.widget.props"
                    :depth-level="1"
            ></widget-setting-complex>

            <widget-setting-primitive
                    v-else
                    v-show="prop.isInShownList"
                    :prop.sync="prop"
            ></widget-setting-primitive>

            <hr v-if="prop.isInShownList && !prop.hidden" class="my-4">
        </div>
    </div>
</template>

<script>
    import {
        fetch_fieldsets_from_props,
        fill_props_is_in_shown_list,
        is_primitive,
    } from "../widgets-helpers";

    import WidgetSettingArray from "./setting/Array.vue";
    import WidgetSettingComplex from "./setting/Complex.vue";
    import WidgetSettingMapping from "./setting/Mapping.vue";
    import WidgetSettingPrimitive from "./setting/Primitive.vue";

    export default {
        components: {
            WidgetSettingArray,
            WidgetSettingComplex,
            WidgetSettingMapping,
            WidgetSettingPrimitive,
        },
        inject: ['$validator'],
        props: ['widget'],
        data() {
            return {
                propsOrFieldsets: {},
            };
        },
        watch: {
            'widget.props': {
                handler(newValue, oldValue) {
                    fill_props_is_in_shown_list(newValue);
                    this.propsOrFieldsets = fetch_fieldsets_from_props(newValue);
                },
                deep: true,
            },
        },
        methods: {
            closeSettings() {
                this.$emit('resetSelectedWidget');
            },
            isPrimitive(prop) {
                return is_primitive(prop);
            },
        },
        mounted() {
            if (this.widget && this.widget.props) {
                this.propsOrFieldsets = fetch_fieldsets_from_props(this.widget.props);
            }
        }
    }
</script>
