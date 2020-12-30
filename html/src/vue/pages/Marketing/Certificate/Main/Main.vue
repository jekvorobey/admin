<template>
  <layout-main back>

    <kpi :kpis="kpis"/>

    <b-card no-body>
      <b-tabs lazy card v-model="tabIndex">
        <b-tab v-for='(tab, key) in tabs' :key="key" :title="tab.title">
          <tab-nominals
              v-if="key === 'nominals'"
              :records="cacheData.nominals"
              @data="updateCache('nominals', $event)"
          />
          <tab-designs
              v-else-if="key === 'designs'"
              :records="cacheData.designs"
              @data="updateCache('designs', $event)"
          />
          <tab-cards
              v-else-if="key === 'cards'"
              :records="cacheData.cards"
              @data="updateCache('cards', $event)"
          />
          <tab-content
              v-else-if="key === 'content'"
              :content="cacheData.content"
              @data="updateCache('content', $event)"
          />
          <tab-reports
              v-else-if="key === 'reports'"
              :records="cacheData.reports"
              @data="updateCache('reports', $event)"
          />
          <tab-logs
              v-else-if="key === 'logs'"
              :records="cacheData.logs"
              @data="updateCache('logs', $event)"
          />
          <template v-else>
            Заглушка
          </template>
        </b-tab>
      </b-tabs>
    </b-card>
  </layout-main>
</template>

<script>
import Kpi from './../components/kpi.vue'

import TabNominals from '../components/tab-nominals.vue';
import TabCards from '../components/tab-cards.vue';
import TabContent from '../components/tab-content.vue';
import TabReports from '../components/tab-reports.vue';
import TabLogs from '../components/tab-logs.vue';
import TabDesigns from '../components/tab-designs.vue';

import tabsMixin from "../../../../mixins/tabs";

export default {
  mixins: [tabsMixin],
  components: {
    Kpi,
    TabNominals,
    TabCards,
    TabContent,
    TabReports,
    TabLogs,
    TabDesigns
  },
  props: [
    'kpis',
    'nominals',
    'designs',
    'cards',
    'content',
    'reports',
    'logs',
  ],
  data() {
    return {
      defaultTabName: 'nominals',
      cacheData: {
        nominals: null,
        designs: null,
        cards: null,
        content: null,
        reports: null,
        logs: null,
      }
    }
  },
  computed: {
    tabs() {
      let tabs = {};
      let i = 0;

      tabs.nominals = {i: i++, title: 'Номиналы'};
      tabs.designs = {i: i++, title: 'Дизайны'};
      tabs.cards = {i: i++, title: 'Купленные ПС'};
      tabs.content = {i: i++, title: 'Контент'};
      tabs.reports = {i: i++, title: 'Отчеты'};
      tabs.logs = {i: i++, title: 'Логи'}

      return tabs;
    },
  },
  created() {
    Object.keys(this.cacheData).forEach(propName => {
      if (this[propName]) {
        this.cacheData[propName] = this[propName];
      }
    })
  },
  methods: {
    updateCache(key, val) {
      this.cacheData[key] = val;
    }
  }
}
</script>
