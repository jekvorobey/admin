import Services from '../../scripts/services/services.js';

export default {
    data() {
        return {
            tabIndex: 0,
            showAllTabs: false,
        };
    },
    watch: {
        'tabIndex': 'pushRoute',
        'showAllTabs': 'pushRoute',
    },
    methods: {
        pushRoute() {
            let route = {};
            if (this.level_id) {
                route.level_id = this.level_id;
            }
            Services.route().push({
                tab: this.currentTabName,
                allTab: this.showAllTabs ? 1 : 0,
            }, location.pathname);
        },
    },
    computed: {
        tabs() {
            return {};
        },
        currentTabName() {
            let tabName = 'main';
            for (let key in this.tabs) {
                if (!this.tabs.hasOwnProperty(key)) {
                    continue;
                }
                if (this.tabs[key].i === this.tabIndex) {
                    tabName = key;
                }
            }

            return tabName;
        }
    },
    created() {
        Services.event().$on('showTab', (tab) => {
            this.tabIndex = this.tabs[tab].i;
        });

        let currentTab = Services.route().get('tab', 'main');
        this.showAllTabs = !!Number(Services.route().get('allTab', 0));
        this.tabIndex = this.tabs[currentTab].i;
    },
}