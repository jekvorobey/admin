<template>
    <layout-main back>
        <b-form @submit.prevent="submit">
            <landing-edit-form
                @update="updateLanding"
                :i-landing="iLanding"
                ></landing-edit-form>

            <b-button v-if="canUpdate(blocks.content)" type="submit" class="mt-3" variant="dark">{{ isCreatingMode ? 'Создать' : 'Обновить' }}</b-button>
            <b-button v-if="canView(blocks.content)" type="button" @click="previewPage()" class="mt-3" variant="light">Предпросмотр</b-button>
            <a :href="previewUrl" target="_blank" ref="previewAnchor" class="d-none"></a>
        </b-form>
    </layout-main>
</template>

<script>
import LandingEditForm from "../../../components/landing/landing-edit-form.vue";
import {mapActions} from "vuex";
import Services from "../../../../scripts/services/services";

export default {
        components: {
            LandingEditForm,
        },
        props: {
            iLanding: {
                type: Object,
                default: {},
            },
            url: String,
        },
        data() {
            return {
                landing: this.iLanding,
                hash: null,
                previewUrl: null,
            }
        },
        methods: {
            ...mapActions({
                showMessageBox: 'modal/showMessageBox',
            }),
            updateLanding(model) {
                this.landing = model;
            },
            submit() {
                if (this.isCreatingMode) {
                    this.create();
                } else {
                    this.update();
                }
            },
            update() {
                Services.showLoader();
                Services.net()
                    .put(this.getRoute('landing.update', {id: this.landing.id}), {}, this.landing)
                    .then((data) => {
                        this.showMessageBox({title: 'Изменения сохранены'});
                        window.location.href = this.route('landing.listPage');
                    })
                    .catch((e) => {
                        this.showMessageBox({title: 'Ошибка', text: 'Попробуйте позже'});
                    }).finally(() => {
                        Services.hideLoader();
                    });
            },
            create() {
                Services.showLoader();
                Services.net()
                    .post(this.getRoute('landing.create'), {}, this.landing)
                    .then((data) => {
                        this.showMessageBox({title: 'Страница сохранена'});
                        window.location.href = this.route('landing.listPage');
                    })
                    .catch(() => {
                        this.showMessageBox({title: 'Ошибка', text: 'Попробуйте позже'});
                    }).finally(() => {
                        Services.hideLoader();
                    });
            },
            previewPage() {
                if (this.hash) {
                    this.landing.hash = this.hash;
                }
                Services.net()
                    .post(this.getRoute('landing.landingCache'), {}, this.landing)
                    .then((data) => {
                        this.hash = data.hash;

                        this.previewUrl = this.url + '/pages/' + this.hash + '?draft=1';
                        setTimeout(() => {
                            this.$nextTick(() => this.$refs.previewAnchor.click());
                        }, 1000);
                    })
                    .catch(() => {
                        this.showMessageBox({title: 'Ошибка', text: 'Попробуйте позже'});
                    });
            },
        },
        computed: {
            isCreatingMode() {
                return !this.landing || this.landing.id == null;
            },
        },
    }
</script>
