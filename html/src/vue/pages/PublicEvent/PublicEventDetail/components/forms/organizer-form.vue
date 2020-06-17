<template>
    <div>
        <div class="form-check">
            <input v-model="fromCatalog" class="form-check-input" type="checkbox" value="" id="catalogOrganizersCheck">
            <label class="form-check-label" for="catalogOrganizersCheck">
                Выбрать из справочника
            </label>
        </div>

        <template v-if="fromCatalog">
            <v-select v-model="$v.selectedOrganizer.$model" :options="organizerOptions">
                Организатор
            </v-select>
        </template>
        <template v-else>
            <v-input v-model="$v.form.name.$model" :error="errorName">
                Название/имя организатора
            </v-input>
            <v-input v-model="$v.form.description.$model" :error="errorDescription">
                Краткое описание
            </v-input>
            <v-input v-model="$v.form.phone.$model" v-mask="telMask" :error="errorPhone">
                Телефон
            </v-input>
            <v-input v-model="$v.form.email.$model" :error="errorEmail">
                E-mail
            </v-input>
            <v-input v-model="$v.form.site.$model" :error="errorSite">
                Сайт
            </v-input>
        </template>

        <button @click="save" class="btn btn-dark">Сохранить</button>
    </div>
</template>

<script>
    import {validationMixin} from 'vuelidate';
    import {required, email, url} from 'vuelidate/lib/validators';

    import {
        NAMESPACE,
        ACT_LOAD_AVAILABLE_ORGANIZERS,
        ACT_SAVE_EVENT_ORGANIZER_ID,
        ACT_SAVE_EVENT_ORGANIZER_VALUE,
    } from '../../../../../store/modules/public-events';

    import VInput from '../../../../../components/controls/VInput/VInput.vue';
    import VSelect from '../../../../../components/controls/VSelect/VSelect.vue';
    import {mapActions} from "vuex";
    import {telMask} from "../../../../../../scripts/mask";

    export default {
    mixins: [validationMixin],
    components: {
        VInput,
        VSelect
    },
    props: {
        eventId:{},
        organizer: {},
    },
    data() {
        this.loadAvailableOrganizers().then(organizers => {
            this.$set(this, 'availableOrganizers', organizers);
        });

        let data = {
            fromCatalog: false,
            availableOrganizers: [],
            selectedOrganizer: null,
            form: {
                name: null,
                description: null,
                phone: null,
                email: null,
                site: null,
            },
        };

        if (this.organizer) {
            if (this.organizer.global) {
                data.fromCatalog = true;
                data.selectedOrganizer = this.organizer.id;
            } else {
                data.form.name = this.organizer.name;
                data.form.description = this.organizer.description;
                data.form.phone = this.organizer.phone;
                data.form.email = this.organizer.email;
                data.form.site = this.organizer.site;
            }
        }

        return data;
    },
    validations: {
        selectedOrganizer: {required},
        form: {
            name: {required},
            description: {required},
            phone: {required},
            email: {required, email},
            site: {url},
        },
    },
    methods: {
        ...mapActions(NAMESPACE, {
            loadAvailableOrganizers: ACT_LOAD_AVAILABLE_ORGANIZERS,
            addById: ACT_SAVE_EVENT_ORGANIZER_ID,
            addByValue: ACT_SAVE_EVENT_ORGANIZER_VALUE,
        }),

        save() {
            this.$v.$touch();
            let promise;
            if (this.fromCatalog) {
                if (this.$v.selectedOrganizer.$invalid) {
                    return;
                }
                promise = this.addById({publicEventId: this.eventId, organizerId: this.selectedOrganizer});
            } else {
                if (this.$v.form.$invalid) {
                    return;
                }
                promise = this.addByValue({publicEventId: this.eventId, organizerData: this.form});
            }

            promise.then(() => {
                this.$emit('onSave');
            });
        }
    },
    computed: {
        telMask() {
            return telMask;
        },

        organizerOptions() {
            return this.availableOrganizers
                .map(organizer => ({text: organizer.name, value: organizer.id}));
        },

        errorName() {
            if (this.$v.form.name.$dirty) {
                if (!this.$v.form.name.required) return "Обязательное поле!";
            }
        },

        errorDescription() {
            if (this.$v.form.description.$dirty) {
                if (!this.$v.form.description.required) return "Обязательное поле!";
            }
        },

        errorPhone() {
            if (this.$v.form.phone.$dirty) {
                if (!this.$v.form.phone.required) return "Обязательное поле!";
            }
        },

        errorEmail() {
            if (this.$v.form.email.$dirty) {
                if (!this.$v.form.email.required) return "Обязательное поле!";
                if (!this.$v.form.email.email) return "Это не email!";
            }
        },

        errorSite() {
            if (this.$v.form.site.$dirty) {
                if (!this.$v.form.site.url) return "Это не ссылка!";
            }
        },
    },
    watch: {
        organizerId(eventOrganizerId) {
            if (eventOrganizerId) {
                for (let organizer of this.availableOrganizers) {
                    if (organizer.id === eventOrganizerId) {
                        this.form.name = organizer.name;
                        this.form.description = organizer.description;
                        this.form.phone = organizer.phone;
                        this.form.email = organizer.email;
                        this.form.site = organizer.site;
                    }
                }
            } else {
                this.form.name = null;
                this.form.description = null;
                this.form.phone = null;
                this.form.email = null;
                this.form.site = null;
            }
        }
    }
}
</script>

<style scoped>

</style>