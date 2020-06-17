<template>
    <div>
        <template>
            <v-select v-model="selectedEventId" text-field="name" value-field="id" :options="events" />
        </template>

        <button @click="save" class="btn btn-dark">Добавить</button>
    </div>
</template>

<script>
    import {
        NAMESPACE,
        ACT_SAVE_EVENT_RECOMMENDATION
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
        events: {}
    },
    data() {
        return {
            selectedEventId: null,
        }
    },
    methods: {
        ...mapActions(NAMESPACE, {
            saveEventRecommendation: ACT_SAVE_EVENT_RECOMMENDATION
        }),
        save() {
            let promise = this.saveEventRecommendation({
                    event_id: this.publicEvent.id,
                    recommendation_id: this.selectedEventId
                });

            promise.then(() => {
                this.$emit('onSave');
            });
        }
    }    
}
</script>