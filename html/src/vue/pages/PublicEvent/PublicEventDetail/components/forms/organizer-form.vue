<template>
    <div>
        <v-select v-model="$v.organizerId.$model" :options="organizerOptions">
            Организатор
        </v-select>
        <v-input v-model="$v.form.name.$model" :error="errorName">
            Название/имя организатора
        </v-input>
        <v-input v-model="$v.form.description.$model" :error="errorName">
            Краткое описание
        </v-input>
        <v-input v-model="$v.form.phone.$model" :error="errorName">
            Телефон
        </v-input>
        <v-input v-model="$v.form.email.$model" :error="errorName">
            E-mail
        </v-input>
        <v-input v-model="$v.form.site.$model" :error="errorName">
            Сайт
        </v-input>
        <button @click="save"  class="btn btn-dark">Сохранить</button>
    </div>
</template>

<script>
import { validationMixin } from 'vuelidate';
import { required } from 'vuelidate/lib/validators';

import {
    NAMESPACE,
    ACT_LOAD_AVAILABLE_ORGANIZERS
} from '../../../../../store/modules/public-events';

import VInput from '../../../../../components/controls/VInput/VInput.vue';
import VSelect from '../../../../../components/controls/VSelect/VSelect.vue';
import {mapActions} from "vuex";

export default {
    mixins: [validationMixin],
    components: {
        VInput,
        VSelect
    },
    props: {
        eventOrganizerId: {},
        organizer: {},
    },
    data() {
        this.availableOrganizers().then(organizers => this.$set(this, 'availableOrganizers', organizers));
        return {
            availableOrganizers: [],
            organizerId: this.eventOrganizerId,
            form: {
                name: this.organizer.name,
                description: this.organizer.description,
                phone: this.organizer.phone,
                email: this.organizer.email,
                site: this.organizer.site,
            },
        };
    },
    validations: {
        organizerId: {required},
        form: {
            name: {required},
            description: {required},
            phone: {required},
            email: {required},
            site: {},
        },
    },
    methods: {
        ...mapActions(NAMESPACE, {
            availableOrganizers: ACT_LOAD_AVAILABLE_ORGANIZERS
        }),
        save() {
            this.$v.$touch();
            if (this.$v.$invalid) {
                return;
            }

            this.savePublicEvent({
                id: this.publicEvent.id,
                data: this.form,
            }).then(() => {
                this.$emit('onSave');
            });
        }
    },
    computed: {
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
            }
        },
        errorSite() {
            if (this.$v.form.site.$dirty) {
                if (!this.$v.form.site.required) return "Обязательное поле!";
            }
        },
    }
}
</script>

<style scoped>

</style>