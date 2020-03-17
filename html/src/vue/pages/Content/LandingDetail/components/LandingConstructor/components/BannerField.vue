<template>
    <div class="form-group"
         :class="{ 'required': prop.required, 'validation-error': errors.has(validationName(prop, true)) }">
        <a class="spoiler"
           :class="{ 'spoiler-checked': showProp }"
           @click="toggleCollapse"
           v-if="prop.spoiler"
        >
            {{ prop.spoiler }}
        </a>

        <div class="widget-settings__item" v-if="prop.label" v-show="showProp">
            <label class="control-label widget-settings__item-label">
                {{ prop.label }}:
            </label>

            <vue-tooltip
                    v-if="prop.tooltip"
                    v-show="prop.isInShownList"
                    :text="prop.tooltip"
                    :link="prop.tooltip_href"
            ></vue-tooltip>

            <v-select2 v-model="prop.value"
                       class="form-control"
                       :multiple="false"
                       :selectOnClose="true"
                       :aria-required="prop.required"
                       width="100%">
                <option v-for="initiator in banners" :value="initiator.id">{{ initiator.name }}</option>
            </v-select2>

            <div class="validation-error-message" v-show="errors.has(validationName(prop, true))">
                {{ errors.first(validationName(prop, true)) }}
            </div>
        </div>
    </div>
</template>

<script>
    import BannerModal from "../../../../ProductGroupDetail/components/banner-modal.vue";
    import modalMixin from "../../../../../../mixins/modal";
    import VSelect2 from "../../../../../../components/controls/VSelect2/v-select2.vue";
    import Services from "../../../../../../../scripts/services/services";

    export default {
        components: {
            VSelect2,
            BannerModal
        },
        mixins: [modalMixin],
        inject: ['$validator'],
        props: ['prop'],
        data() {
            return {
                showProp: true,
                banners: [],
            };
        },
        methods: {
            toggleCollapse() {
                this.showProp = !this.showProp;
            },
            validationName(prop, full = false) {
                const unique_code = prop.code + this._uid;
                return full ? 'content.' + unique_code : unique_code;
            },
            onModalAccept(id) {
                this.prop.value = id;
                this.closeModal('FormTheme');
            },
            openBannerModal() {
                this.openModal('FormTheme');
            },
            removeBanner() {
                this.prop.value = null;
            },
            loadBanners() {
                Services.net()
                    .get(this.getRoute('banner.widgetBanners'))
                    .then((data) => {
                        this.banners = data;
                    })
                    .catch((e) => {
                        console.error(e);
                    });
            },
        },
        computed: {},
        mounted() {
            this.showProp = !this.prop.hidden && !this.prop.spoiler;

            this.loadBanners();
        }
    }
</script>
