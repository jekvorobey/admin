<template>
    <div>
        <template>
            <v-select v-model="selectedSpecialtyId" :options="specialtiesOptions"/>
        </template>
        <button @click="save" class="btn btn-dark">Добавить</button>
    </div>
</template>

<script>
import {
    NAMESPACE,
    ACT_LOAD_EVENT_SPECIALTIES,
    ACT_SAVE_EVENT_SPECIALTY
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
        allSpecialtiesList: {},
    },
    data() {
        return {
            specialty: {},
            selectedSpecialtyId: null,
        }
    },
    methods: {
        ...mapActions(NAMESPACE, {
            loadPublicEventSpecialties: ACT_LOAD_EVENT_SPECIALTIES,
            savePublicEventSpecialty: ACT_SAVE_EVENT_SPECIALTY
        }),
        getSpecialties() {
            let promise = this.loadPublicEventSpecialties({publicEventId: this.publicEvent.id});

            promise.then(response => {
                this.specialties = response.specialties;
            });
        },
        save() {
            let promise = this.savePublicEventSpecialty({
                publicEventId: this.publicEvent.id,
                id: this.selectedSpecialtyId
            });

            promise.then(() => {
                this.$emit('onSave');
            });
        },
    },
    computed: {
        specialtiesOptions() {
            return Object.values(this.allSpecialtiesList).map(specialty => ({value: specialty.id, text: specialty.name}));
        },
    },
    created() {
        this.getSpecialties();
    }
}
</script>