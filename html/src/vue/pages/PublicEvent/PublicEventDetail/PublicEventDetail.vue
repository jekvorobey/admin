<template>
    <layout-main back>
        <div class="d-flex flex-wrap align-items-stretch justify-content-start product-header">
            <div class="shadow flex-grow-3 mr-3 mt-3">
                <h2>{{ event.name }}</h2>
                <p class="text-secondary">ID: <span class="float-right">{{ event.id }}</span></p>
                <p v-if="event.organizer" class="text-secondary">Организатор: <span class="float-right">{{ event.organizer.name }}</span></p>
                <template v-if="event.actualSprint">
                    <p class="text-secondary">Дата начала: <span class="float-right">{{ event.actualSprint.date_start }}</span></p>
                    <p class="text-secondary">Дата окончания: <span class="float-right">{{ event.actualSprint.date_end }}</span></p>
                    <p v-if="event.actualSprint.place" class="text-secondary">Место проведения: <span class="float-right">{{ event.actualSprint.place.name }}</span></p>
                    <p class="text-secondary">Билеты: <span class="float-right">{{ event.actualSprint.ticketsSoldCount }} / {{ event.actualSprint.totalTicketsCount }}</span></p>
                </template>
            </div>

        </div>
    </layout-main>
</template>

<script>

    import VTabs from '../../../components/tabs/tabs.vue';

    import Services from '../../../../scripts/services/services';
    import modalMixin from '../../../mixins/modal';

    export default {
    components: {
        VTabs,

    },
    mixins: [modalMixin],
    props: {
        iPublicEvent:{}
    },
    data() {
        return {
            event: this.iPublicEvent
        };
    },

    methods: {

    },
    computed: {
        title() {
            return this.$store.state.title;
        },

    },
};
</script>
<style scoped>
    .product-header {
        min-height: 200px;
    }
    .product-header > div {
        padding: 16px;
    }
    .product-header img {
        max-height: calc( 200px - 16px * 2 );
    }
    .product-header p {
        margin: 0;
        padding: 0;
    }
    .measure {
        width: 30px;
        margin-left: 10px;
    }
</style>
