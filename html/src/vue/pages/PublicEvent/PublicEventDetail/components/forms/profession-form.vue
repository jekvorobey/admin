<template>
    <div>
        <template>
            <v-select v-model="selectedProfessionId" text-field="name" value-field="id" disabled-field="active" :options="professions" />
        </template>

        <button @click="save" class="btn btn-dark">Добавить</button>
    </div>
</template>

<script>
    import {
        NAMESPACE,
        ACT_LOAD_PROFESSIONS,
        ACT_SAVE_EVENT_PROFESSION
    } from '../../../../../store/modules/public-events';

    import VSelect from '../../../../../components/controls/VSelect/VSelect.vue';
    import VInput from '../../../../../components/controls/VInput/VInput.vue';
    import {mapActions} from "vuex";

    export default {
    components: {
        VInput,
        VSelect
    },
    props: {
        publicEvent: {},
    },
    data() {
        return {
            professions: [],
            selectedProfessionId: null,
        }
    },
    methods: {
        ...mapActions(NAMESPACE, {
            loadProfessions: ACT_LOAD_PROFESSIONS,
            savePublicEventProfession: ACT_SAVE_EVENT_PROFESSION
        }),
        getProfessions() {
            this.loadProfessions()
                .then(response => {
                    this.professions = response.professions;
                    if (this.professions.length) {
                        this.professions.forEach(profession => {
                                profession.active = !profession.active;
                            });
                    }
                });
        },
        save() {
            let promise = this.savePublicEventProfession({
                    publicEventProfession: {
                        profession_id: this.selectedProfessionId,
                        public_event_id: this.publicEvent.id
                    }
                });

            promise.then(() => {
                this.$emit('onSave');
            });
        }
    },
    created() {
        this.getProfessions();
    }
        
}
</script>