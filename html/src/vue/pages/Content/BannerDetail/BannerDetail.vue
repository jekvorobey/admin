<template>
    <layout-main back>
        <b-form @submit.prevent="submit">
            <b-form-group
                    label="Наименование*"
                    label-for="group-name"
            >
                <b-form-input
                        id="group-name"
                        v-model="banner.name"
                        type="text"
                        required
                        placeholder="Введите наименование"
                />
            </b-form-group>

            <b-form-group
                    label="Активность"
                    label-for="group-active"
            >
                <b-form-checkbox
                        id="group-active"
                        v-model="banner.active"
                        :value="1"
                        :unchecked-value="0"
                        required
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
            />

            <b-form-group
                    label="Тип*"
                    label-for="group-type"
            >
                <b-form-select
                        v-model="banner.type_id"
                        id="group-type"
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

            <b-button type="submit" class="mt-3" variant="dark">{{ isCreatingMode ? 'Создать' : 'Обновить' }}</b-button>
        </b-form>
    </layout-main>
</template>

<script>
    import {mapActions, mapGetters} from "vuex";
    import Services from "../../../../scripts/services/services";
    import FileInput from '../../../components/controls/FileInput/FileInput.vue';

    export default {
        components: {
            FileInput
        },
        props: {
            iBanner: {},
            iBannerTypes: {},
            iBannerImages: {},
            options: {}
        },
        data() {
            return {
                banner: this.normalizeProductGroup(this.iBanner),
                bannerTypes: this.iBannerTypes,
                bannerImages: this.iBannerImages,
            };
        },

        methods: {
            ...mapActions({
                showMessageBox: 'modal/showMessageBox',
            }),
            normalizeProductGroup(source) {
                return {
                    id: source.id ? source.id : null,
                    name: source.name ? source.name : null,
                    active: source.active ? source.active : false,
                    type_id: source.type_id ? source.type_id : null,
                    desktop_image_id: source.desktop_image_id ? source.desktop_image_id : null,
                };
            },
            submit() {
                if (this.isCreatingMode) {
                    this.create();
                } else {
                    this.update();
                }
            },
            update() {
                let model = this.banner;

                Services.net()
                    .put(this.getRoute('banner.update', {id: this.banner.id,}), {}, model)
                    .then((data) => {
                        this.showMessageBox({title: 'Изменения сохранены'});
                        window.location.href = this.route('banner.list');
                    })
                    .catch(() => {
                        this.showMessageBox({title: 'Ошибка', text: 'Попробуйте позже'});
                    });
            },
            create() {
                let model = this.banner;

                Services.net()
                    .post(this.getRoute('banner.create'), {}, model)
                    .then((data) => {
                        this.showMessageBox({title: 'Страница сохранена'});
                        window.location.href = this.route('banner.list');
                    })
                    .catch(() => {
                        this.showMessageBox({title: 'Ошибка', text: 'Попробуйте позже'});
                    });
            },
            onUploadDesktopImage(file) {
                this.bannerImages[file.id] = file;
                this.banner.desktop_image_id = file.id;
            },
        },
        computed: {
            ...mapGetters(['getRoute']),
            desktopImage() {
                const fileId = this.banner.desktop_image_id;

                if (fileId) {
                    const file = this.bannerImages[fileId];

                    return file ? file : null;
                }

                return null;
            },
            isCreatingMode() {
                return this.banner.id === null;
            },
        },
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
