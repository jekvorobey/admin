<template>
    <layout-main back>
        <b-form @submit.prevent="submit">
            <banner-edit-form
                    @update="updateBanner"
                    :iBanner="iBanner"
                    :iBannerTypes="iBannerTypes"
                    :iBannerButtonTypes="iBannerButtonTypes"
                    :iBannerButtonLocations="iBannerButtonLocations"
                    :iBannerImages="iBannerImages"
                    @isOpen="isOpenChange"
                    :is-open="isOpen"
            ></banner-edit-form>

            <b-button v-if="canUpdate(blocks.content)" type="submit" class="mt-3" variant="dark">{{ isCreatingMode ? 'Создать' : 'Обновить' }}</b-button>
        </b-form>
    </layout-main>
</template>

<script>
    import {mapActions} from 'vuex';
    import Services from '../../../../scripts/services/services';
    import BannerEditForm from "../../../components/banner-edit-form/banner-edit-form.vue";

    export default {
        components: {
            BannerEditForm,
        },
        props: {
            iBanner: Object,
            iBannerTypes: Array,
            iBannerButtonTypes: Array,
            iBannerButtonLocations: Array,
            iBannerImages: Object,
            options: Object
        },
        data() {
            return {
                banner: this.iBanner,
                isOpen: /isOpen/.test(this.iBanner.url)
            };
        },
        methods: {
            ...mapActions({
                showMessageBox: 'modal/showMessageBox',
            }),
            updateBanner(model) {
                this.banner = model;
            },
            isOpenChange(value) {
                this.isOpen = value;
            },
            customizeUrlIsOpen() {
                if (this.isOpen && !/isOpen/.test(this.banner.url)) {
                    if (this.banner.url.includes('?')) {
                        if (this.banner.url[this.banner.url.length - 1] === '/' ){
                            this.banner.url = this.banner.url.slice(0, this.banner.url.length - 1) + '&isOpen=true'
                        } else this.banner.url = this.banner.url + '&isOpen=true'
                    } else {
                        if (this.banner.url[this.banner.url.length - 1] === '/' ){
                            this.banner.url = this.banner.url + '?isOpen=true';
                        } else this.banner.url = this.banner.url + '/?isOpen=true';
                    }
                }

                if (!this.isOpen && /\/\?isOpen=true/.test(this.banner.url)) {
                    this.banner.url = this.banner.url.replace(/\/\?isOpen=true/, '')
                }

                if (!this.isOpen && /&isOpen=true/.test(this.banner.url)) {
                    this.banner.url = this.banner.url.replace(/&isOpen=true/, '')
                }
            },
            submit() {
                this.customizeUrlIsOpen()

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
                        window.location.href = this.route('banner.listPage');
                    })
                    .catch((e) => {
                        this.showMessageBox({title: 'Ошибка', text: 'Попробуйте позже'});
                    });
            },
            create() {
                let model = this.banner;

                Services.net()
                    .post(this.getRoute('banner.create'), {}, model)
                    .then((data) => {
                        this.showMessageBox({title: 'Страница сохранена'});
                        window.location.href = this.route('banner.listPage');
                    })
                    .catch(() => {
                        this.showMessageBox({title: 'Ошибка', text: 'Попробуйте позже'});
                    });
            },
        },
        computed: {
            isCreatingMode() {
                return !this.banner || this.banner.id == null;
            },
        },
    };
</script>

<style scoped>
</style>
