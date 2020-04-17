<template>
    <layout-main back>
        <div class="d-flex flex-wrap align-items-stretch justify-content-start product-header">
            <div class="shadow flex-grow-3 mr-3 mt-3">
                <h2>{{ publicEvent.name }}</h2>
                <p class="text-secondary">ID: <span class="float-right">{{ publicEvent.id }}</span></p>
                <p v-if="publicEvent.organizer" class="text-secondary">Организатор: <span class="float-right">{{ publicEvent.organizer.name }}</span></p>
                <template v-if="publicEvent.actualSprint">
                    <p class="text-secondary">Дата начала: <span class="float-right">{{ publicEvent.actualSprint.date_start }}</span></p>
                    <p class="text-secondary">Дата окончания: <span class="float-right">{{ publicEvent.actualSprint.date_end }}</span></p>
                    <p v-if="publicEvent.actualSprint.place" class="text-secondary">Место проведения: <span class="float-right">{{ publicEvent.actualSprint.place.name }}</span></p>
                    <p class="text-secondary">Билеты: <span class="float-right">{{ publicEvent.actualSprint.ticketsSoldCount }} / {{ publicEvent.actualSprint.totalTicketsCount }}</span></p>
                </template>
            </div>
        </div>
        <v-tabs :current="nav.currentTab" :items="nav.tabs" @nav="tab => nav.currentTab = tab"/>
        <main-tab
                v-if="nav.currentTab === 'main'"
                :public-event="publicEvent"
                @onChange="onTabChange"
        />
        <content-tab
                v-if="nav.currentTab === 'content'"
                :public-event="publicEvent"
                @onChange="onTabChange"
        />
    </layout-main>
</template>

<script>
    import {mapActions, mapGetters} from "vuex";

    import VTabs from '../../../components/tabs/tabs.vue';

    import MainTab from './components/main-tab.vue';
    import ContentTab from './components/content-tab.vue';

    import modalMixin from '../../../mixins/modal';
    import {
        NAMESPACE,
        SET_DETAIL,
        GET_DETAIL,
        ACT_LOAD_PUBLIC_EVENT,
    } from '../../../store/modules/public-events';

    export default {
    components: {
        VTabs,
        MainTab,
        ContentTab,
    },
    mixins: [modalMixin],
    props: {
        iPublicEvent:{}
    },
    data() {
        this.$store.commit(`${NAMESPACE}/${SET_DETAIL}`, {publicEvent: this.iPublicEvent});

        return {
            nav: {
                currentTab: 'main',
                tabs: [
                    {value: 'main', text: 'Основное'},
                    {value: 'content', text: 'Контент'},
                ]
            }
        };
    },

    methods: {
        ...mapActions(NAMESPACE, {
            reload: ACT_LOAD_PUBLIC_EVENT
        }),
        onTabChange() {
            this.reload({id: this.publicEvent.id});
        }
    },
    computed: {
        ...mapGetters(NAMESPACE, {
            publicEvent: GET_DETAIL
        })
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
