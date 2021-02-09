import Services from '../../../../../scripts/services/services';

export default {
    props: ['records'],
    data() {
        return {
            loading: false,
            currentPage: 1,
            filter: {},
            tabName: '__setup__'
        };
    },
    methods: {
        applyFilter() {
            this.loadPage(1);
        },
        getFilter() {
            return this.filter
        },
        loadPage(page = null) {
            this.loading = true;
            Services.showLoader();
            const params = {
                page: page || this.currentPage,
                filter: this.getFilter()
            }
            this.currentPage = params.page
            return Services.net().get(this.getRoute('certificate.tab', {tab: this.tabName}), params).then(response => {
                this.$emit('data', response[this.tabName])
                Services.hideLoader();
                this.loading = false;
            })
        },
    },
    computed: {
        items() {
            return (this.records && this.records.hasOwnProperty('items')) ? this.records.items : [];
        },
        pagination() {
            return (this.records && this.records.hasOwnProperty('pagination')) ? this.records.pagination : {};
        },
        pager() {
            return (this.records && this.records.hasOwnProperty('pager')) ? this.records.pager : {};
        },
        recordsAmount() {
            return this.pager.total || this.pagination.total || 0
        }
    },
    mounted() {
        if (this.records === null) {
            this.loadPage();
        } else {
            this.currentPage = this.records.page || 1
        }
    }
}