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

            <b-button v-show="prop.value" class="my-3" variant="outline-primary" @click.prevent="openBannerModal">Изменить баннер</b-button>
            <b-button v-show="prop.value" class="my-3" variant="outline-primary" @click.prevent="removeBanner">Удалить баннер</b-button>
            <b-button v-show="!prop.value" class="my-3" variant="outline-primary" @click.prevent="openBannerModal">Создать баннер</b-button>

            <banner-modal modal-name="FormTheme" @accept="onModalAccept" :id="prop.value"/>

            <div class="validation-error-message" v-show="errors.has(validationName(prop, true))">
                {{ errors.first(validationName(prop, true)) }}
            </div>
        </div>
    </div>
</template>

<script>
    import BannerModal from "../../../../ProductGroupDetail/components/banner-modal.vue";
    import modalMixin from "../../../../../../mixins/modal";

    export default {
        components: {
            BannerModal
        },
        mixins: [modalMixin],
        inject: ['$validator'],
        props: ['prop'],
        data() {
            return {
                showProp: true,
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
        },
        computed: {
        },
        mounted() {
            this.showProp = !this.prop.hidden && !this.prop.spoiler;
        }
    }
</script>
