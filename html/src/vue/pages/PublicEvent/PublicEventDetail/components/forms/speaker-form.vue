<template>
    <div>
        <template>
            <v-select v-model="selectedSpeakerId" text-field="name" value-field="id" :options="speakers" />
        </template>

        <button @click="save" class="btn btn-dark">Добавить</button>
    </div>
</template>

<script>
    import {
        NAMESPACE,
        ACT_LOAD_SPEAKERS,
        ACT_SAVE_EVENT_SPEAKER
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
        sprintStageId: {},
    },
    data() {
        return {
            speakers: [],
            selectedSpeakerId: null,
        }
    },
    methods: {
        ...mapActions(NAMESPACE, {
            loadSpeakers: ACT_LOAD_SPEAKERS,
            saveEventSpeaker: ACT_SAVE_EVENT_SPEAKER
        }),
        getSpeakers() {
            this.loadSpeakers()
                .then(data => {
                    this.speakers = data.speakers;
                    this.speakers.forEach(speaker => {
                        speaker.name = this.name(speaker);
                    });
                });
        },
        name(speaker) {
            return `${speaker.first_name} ${speaker.middle_name} ${speaker.last_name}`;
        },
        save() {
            let promise = this.saveEventSpeaker({
                stageId: this.sprintStageId,
                id: this.selectedSpeakerId
            });

            promise.then(() => {
                this.$emit('onSave');
            });
        }
    },
    created() {
        this.getSpeakers();
    }
        
}
</script>