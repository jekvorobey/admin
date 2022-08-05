<template>
    <div>
        <b-form-group
                label="Наименование*"
                label-for="banner-group-name"
        >
            <b-form-input
                    id="banner-group-name"
                    v-model="banner.name"
                    type="text"
                    required
                    placeholder="Введите наименование"
            />
        </b-form-group>

        <b-form-group
                label="Активность"
                label-for="banner-group-active"
        >
            <b-form-checkbox
                    id="banner-group-active"
                    v-model="banner.active"
                    :value="1"
                    :unchecked-value="0"
            />
        </b-form-group>

        <b-form-group label="Начало" label-for="banner-date-from">
            <date-picker
                v-model="banner.date_from"
                id="banner-date-from"
                type="datetime"
                input-class="form-control"
                format="YYYY-MM-DD HH:mm"
                value-type="format"
            />
        </b-form-group>

        <b-form-group label="Конец" label-for="banner-date-to">
            <date-picker
                v-model="banner.date_to"
                id="banner-date-to"
                type="datetime"
                input-class="form-control"
                format="YYYY-MM-DD HH:mm"
                value-type="format"
                @change="onChangeDateTo"
            />
        </b-form-group>

        <b-form-group
            label="Тип*"
            label-for="banner-group-type"
        >
            <b-form-select
                v-model="banner.type_id"
                id="banner-group-type"
                required
            >
                <b-form-select-option
                    :value="type.id"
                    v-for="type in bannerTypes"
                    :key="type.id"
                >
                    {{ type.name }}
                </b-form-select-option>
            </b-form-select>
        </b-form-group>

        <b-form-group v-if="showColorField" label="Цвет" label-for="banner-color">
            <div class="d-flex align-items-center">
                <div>
                    <vue-swatches v-model="banner.color" id="banner-color" show-fallback fallback-input-type="color" />
                </div>
                <div class="ml-2">
                    <b-button size="sm" @click="banner.color = null">Сбросить</b-button>
                </div>
            </div>
            <div>
                HEX: {{ banner.color ? banner.color : 'Не выбран' }}
            </div>
        </b-form-group>

        <b-form-group v-if="showControlsColorFields" label="Цвет управления и кнопок" label-for="banner-controls-color">
            <div class="d-flex align-items-center">
                <div>
                    <vue-swatches v-model="banner.controls_color" id="banner-controls-color" show-fallback fallback-input-type="color" />
                </div>
                <div class="ml-2">
                    <b-button size="sm" @click="banner.controls_color = null">Сбросить</b-button>
                </div>
            </div>
            <div>
                HEX: {{ banner.controls_color ? banner.controls_color : 'Не выбран' }}
            </div>
        </b-form-group>

        <b-form-group v-if="showPathTemplatesField" label="Страницы" label-for="banner-path-templates">
            <b-form-textarea v-model="banner.path_templates" id="banner-path-templates" />
        </b-form-group>

        <b-form-group v-if="bannerIsCatalogTop" label="Дополнительный текст" id="additional-text" label-for="banner-additional-text">
            <ckeditor v-model="banner.additional_text" :editor="editor" :config="editorSettings" />
        </b-form-group>

        <b-form-group label="Сортировка" label-for="banner-sort">
            <b-form-input
                v-model="banner.sort"
                id="banner-sort"
                step="1"
                type="number"
                pattern="[0-9]"
                @keydown="disallowDecimal"
            />
        </b-form-group>

        Десктоп изображение*<br>
        <img v-if="desktopImage"
             :src="desktopImage.url"
             class="preview-photo"
        >
        <file-input
                @uploaded="onUploadDesktopImage"
                class="preview-photo-input"
                :required="!desktopImage"
        />

        Планшетное изображение<br>
        <img v-if="tabletImage"
             :src="tabletImage.url"
             class="preview-photo"
        >
        <file-input
                @uploaded="onUploadTabletImage"
                class="preview-photo-input"
        />

        Мобильное изображение<br>
        <img v-if="mobileImage"
             :src="mobileImage.url"
             class="preview-photo"
        >
        <file-input
                @uploaded="onUploadMobileImage"
                class="preview-photo-input"
        />

        <b-form-group
                label="Ссылка"
                label-for="banner-group-url"
        >
            <b-form-input
                    id="banner-group-url"
                    v-model="banner.url"
                    type="text"
                    placeholder="Введите ссылку"
            />
        </b-form-group>
    </div>
</template>

<script>
    import {mapActions} from 'vuex';
    import VueCkeditor from '../../plugins/VueCustomCkeditor';
    import CustomEditor from 'ckeditor5-custom-build';
    import FileInput from '../controls/FileInput/FileInput.vue';

    import DatePicker from 'vue2-datepicker';
    import moment from "moment";
    import 'vue2-datepicker/index.css';
    import 'vue2-datepicker/locale/ru.js';

    import VueSwatches from 'vue-swatches';
    import 'vue-swatches/dist/vue-swatches.css';

    const bannerType = Object.freeze({
        mainTop: 6,
        mainNew: 7,
        mainMiddle: 8,
        mainBest: 9,
        catalogTop: 2,
    });

    export default {
        components: {
            FileInput,
            DatePicker,
            VueSwatches,
            VueCkeditor
        },

        props: {
            iBanner: Object,
            iBannerTypes: Array,
            iBannerButtonTypes: Array,
            iBannerButtonLocations: Array,
            iBannerImages: [Object, Array],
            options: Object
        },

        data() {
            return {
                banner: this.normalizeBanner(this.iBanner),
                bannerTypes: this.iBannerTypes,
                bannerButtonTypes: this.iBannerButtonTypes,
                bannerButtonLocations: this.iBannerButtonLocations,
                bannerImages: this.iBannerImages,
                hasButton: this.initHasButton(),

                editor: CustomEditor,
                editorSettings: {
                    mediaEmbed: {
                        previewsInData: true,
                    },
                    simpleFileUpload: {
                        fileTypes: [
                            '.pdf',
                            '.doc',
                            '.docx',
                            '.xls',
                            '.xlsx'
                        ],
                        destination: 'landing',
                        url: this.route('uploadFile'),
                    },
                }
            };
        },

        methods: {
            ...mapActions({
                showMessageBox: 'modal/showMessageBox',
            }),

            disallowDecimal(event) {
                if (event.key === '.') {
                    event.preventDefault();
                }
            },

            initHasButton() {
                return this.iBanner.button !== null ? 1 : 0;
            },
            normalizeBanner(source) {
                return {
                    id: source.id ? source.id : null,
                    name: source.name ? source.name : null,
                    url: source.url ? source.url : null,
                    active: source.active ? source.active : false,
                    type_id: source.type_id ? source.type_id : null,
                    desktop_image_id: source.desktop_image_id ? source.desktop_image_id : null,
                    tablet_image_id: source.tablet_image_id ? source.tablet_image_id : null,
                    mobile_image_id: source.mobile_image_id ? source.mobile_image_id : null,
                    button: this.normalizeButton(source.button || {}),
                    date_from: source.date_from ? source.date_from : null,
                    date_to: source.date_to ? source.date_to : null,
                    color: source.color ? source.color : null,
                    controls_color: source.controls_color ? source.controls_color : null,
                    path_templates: source.path_templates ? source.path_templates : null,
                    sort: source.sort ? source.sort : null,
                    additional_text: source.additional_text ? source.additional_text : null,
                };
            },
            normalizeButton(source) {
                return {
                    id: source.id ? source.id : null,
                    text: source.text ? source.text : null,
                    type: source.type ? source.type : null,
                    location: source.location ? source.location : null,
                };
            },
            onUploadDesktopImage(file) {
                this.bannerImages[file.id] = file;
                this.banner.desktop_image_id = file.id;
            },
            onUploadTabletImage(file) {
                this.bannerImages[file.id] = file;
                this.banner.tablet_image_id = file.id;
            },
            onUploadMobileImage(file) {
                this.bannerImages[file.id] = file;
                this.banner.mobile_image_id = file.id;
            },
            onChangeDateTo(value) {
                let dateTime = new Date(value);
                if (dateTime && !dateTime.getHours() && !dateTime.getMinutes() && !dateTime.getSeconds()) {
                    dateTime.setHours(23, 59, 59);
                    this.banner.date_to = moment(dateTime).format("YYYY-MM-DD HH:mm");
                }
            },
        },

        computed: {
            desktopImage() {
                const fileId = this.banner.desktop_image_id;

                if (fileId) {
                    return this.bannerImages[fileId] || null;
                }

                return null;
            },

            tabletImage() {
                const fileId = this.banner.tablet_image_id;

                if (fileId) {
                    return this.bannerImages[fileId] || null;
                }

                return null;
            },

            mobileImage() {
                const fileId = this.banner.mobile_image_id;

                if (fileId) {
                    return this.bannerImages[fileId] || null;
                }

                return null;
            },

            isCreatingMode() {
                return this.banner.id == null;
            },

            showColorField() {
                if (this.banner && this.banner.type_id === bannerType.mainTop) {
                    return true;
                }

                return false;
            },

            showControlsColorFields() {
                if (
                    this.banner &&
                    (
                        this.banner.type_id === bannerType.mainTop ||
                        this.banner.type_id === bannerType.catalogTop
                    )
                ) {
                    return true;
                }

                return false;
            },

            showPathTemplatesField() {
                if (!this.banner || !this.banner.type_id) {
                    return false;
                }

                if (
                    this.banner.type_id === bannerType.mainTop ||
                    this.banner.type_id === bannerType.mainNew ||
                    this.banner.type_id === bannerType.mainMiddle ||
                    this.banner.type_id === bannerType.mainBest
                ) {
                    return false;
                }

                return true;
            },

            bannerIsCatalogTop() {
                if (this.banner) {
                    if (this.banner.type_id === bannerType.catalogTop) {
                        return true;
                    }
                }

                return false;
            }
        },
        watch: {
            banner: {
                deep: true,
                handler() {
                    this.$emit('update', this.banner);
                },
            },
        }
    };
</script>

<style scoped>
    .preview-photo {
        width: 300px;
    }

    .preview-photo-input {
        width: 300px;
        margin-top: 10px;
    }
</style>
