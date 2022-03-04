<template>
    <layout-main back>
        <b-form @submit.prevent="submit">
            <b-form-group
                label="Наименование*"
                label-for="seo-group-name"
            >
                <b-form-input
                    id="seo-group-name"
                    v-model="seo.name"
                    type="text"
                    required
                    placeholder="Введите наименование"
                />
            </b-form-group>
            <b-form-group
                label="Мета заголовок*"
                label-for="seo-group-meta-title"
            >
                <b-form-input
                    id="seo-group-meta-title"
                    v-model="seo.meta_title"
                    type="text"
                    required
                    placeholder="Введите мета заголовок"
                />
            </b-form-group>
            <b-form-group
                label="Мета описание*"
                label-for="seo-group-meta-title"
            >
                <b-form-textarea
                    id="seo-group-meta-description"
                    v-model="seo.meta_description"
                    required
                    placeholder="Введите мета описание"
                />
            </b-form-group>

            <b-button type="submit" class="mt-3" variant="dark">Обновить</b-button>
        </b-form>
    </layout-main>
</template>

<script>

    import Services from "../../../../scripts/services/services";
    import {mapActions} from "vuex";

    export default {
        props: {
            iSeo: Object,
        },
        data() {
            return {
                seo: this.iSeo
            };
        },
        methods: {
            ...mapActions({
                showMessageBox: 'modal/showMessageBox',
            }),
            submit() {
                Services.net()
                    .put(this.getRoute('seo.update', {id: this.seo.id,}), {}, this.seo)
                    .then((data) => {
                        this.showMessageBox({title: 'Изменения сохранены'});
                        window.location.href = this.route('seo.listPage');
                    })
                    .catch((e) => {
                        this.showMessageBox({title: 'Ошибка', text: 'Попробуйте позже'});
                    });
            }
        },
    }
</script>
