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

        <b-form-group
                label="С кнопкой?"
                label-for="banner-group-has-button"
        >
            <b-form-checkbox
                    id="banner-group-has-button"
                    v-model="hasButton"
                    :value="1"
                    :unchecked-value="0"
            />
        </b-form-group>

        <div v-show="hasButton">
            Кнопка
        </div>
        <div v-show="hasButton" class="border border-dark rounded p-2">
            <b-form-group
                    label="Ссылка*"
                    label-for="banner-group-button-url"
            >
                <b-form-input
                        id="banner-group-button-url"
                        v-model="banner.button.url"
                        type="text"
                        :required="!!hasButton"
                        placeholder="Введите ссылку"
                />
            </b-form-group>

            <b-form-group
                    label="Текст*"
                    label-for="banner-group-button-text"
            >
                <b-form-input
                        id="banner-group-button-text"
                        v-model="banner.button.text"
                        type="text"
                        :required="!!hasButton"
                        placeholder="Введите текст"
                />
            </b-form-group>

            <b-form-group
                    label="Тип*"
                    label-for="banner-group-button-type"
            >
                <b-form-select
                        v-model="banner.button.type"
                        id="banner-group-button-type"
                        :required="!!hasButton"
                >
                    <b-form-select-option
                            :value="bannerButtonType.code"
                            v-for="bannerButtonType in bannerButtonTypes"
                            :key="bannerButtonType.code"
                    >
                        {{ bannerButtonType.name }}
                    </b-form-select-option>
                </b-form-select>
            </b-form-group>

            <b-form-group
                    label="Местоположение*"
                    label-for="banner-group-button-location"
            >
                <b-form-select
                        v-model="banner.button.location"
                        id="banner-group-button-location"
                        :required="!!hasButton"
                >
                    <b-form-select-option
                            :value="bannerButtonLocation.code"
                            v-for="bannerButtonLocation in bannerButtonLocations"
                            :key="bannerButtonLocation.code"
                    >
                        {{ bannerButtonLocation.name }}
                    </b-form-select-option>
                </b-form-select>
            </b-form-group>
        </div>
    </div>
</template>

<script>
    import {mapActions} from 'vuex';
    import FileInput from '../controls/FileInput/FileInput.vue';

    export default {
        components: {
            FileInput
        },
        props: {
            iBanner: {},
            iBannerTypes: [],
            iBannerButtonTypes: [],
            iBannerButtonLocations: [],
            iBannerImages: {},
            options: {}
        },
        data() {
            return {
                banner: this.normalizeBanner(this.iBanner),
                bannerTypes: this.iBannerTypes,
                bannerButtonTypes: this.iBannerButtonTypes,
                bannerButtonLocations: this.iBannerButtonLocations,
                bannerImages: this.iBannerImages,
                hasButton: this.initHasButton(),
            };
        },

        methods: {
            ...mapActions({
                showMessageBox: 'modal/showMessageBox',
            }),
            initHasButton() {
                return this.iBanner.button !== null ? 1 : 0;
            },
            normalizeBanner(source) {
                return {
                    id: source.id ? source.id : null,
                    name: source.name ? source.name : null,
                    active: source.active ? source.active : false,
                    type_id: source.type_id ? source.type_id : null,
                    desktop_image_id: source.desktop_image_id ? source.desktop_image_id : null,
                    tablet_image_id: source.tablet_image_id ? source.tablet_image_id : null,
                    mobile_image_id: source.mobile_image_id ? source.mobile_image_id : null,
                    button: this.normalizeButton(source.button || {}),
                };
            },
            normalizeButton(source) {
                return {
                    id: source.id ? source.id : null,
                    url: source.url ? source.url : null,
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
                return this.banner.id === null;
            },
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
