<template>
    <layout-main back>
        <b-form @submit.prevent="submit">
            <banner-edit-form
                    @update="updateBanner"
                    :iBanner="iBanner"
                    :iBannerTypes="iBannerTypes"
                    :iBannerCountdown="iBannerCountdown"
                    :iBannerCountdownImages="iBannerCountdownImages"
                    :iBannerButtonTypes="iBannerButtonTypes"
                    :iBannerButtonLocations="iBannerButtonLocations"
                    :iBannerImages="iBannerImages"
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
            iBannerCountdown: Object,
            iBannerCountdownImages: Object,
            iBannerButtonTypes: Array,
            iBannerButtonLocations: Array,
            iBannerImages: Object,
            options: Object
        },
        data() {
            return {
                banner: this.iBanner
            };
        },
        methods: {
            ...mapActions({
                showMessageBox: 'modal/showMessageBox',
            }),
            updateBanner(model) {
                this.banner = model;
            },
            submit() {
                if (this.isCreatingMode) {
                    this.create();
                } else {
                    this.update();
                }
            },
            update() {
                this.banner.bannerCountdown = this.iBannerCountdown;
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
