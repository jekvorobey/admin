<template>
    <layout-main back>
        <div class="row align-items-stretch justify-content-start claim-header">
            <div class="col-md-6">
                <div class="shadow p-3 height-100">
                    <h2>Заявка #{{ claim.id }} от {{ claim.created_at }}</h2>
                    <div style="height: 40px">
                        <span class="badge" :class="statusClass(claim.status)">
                            {{ statusName(claim.status) || 'N/A' }}
                        </span>
                        <button class="btn btn-primary" v-if="isNewStatus"
                                @click="changeClaimStatus(2)">В работу</button>
                        <button class="btn btn-primary" v-if="isWorkStatus"
                                @click="changeClaimStatus(3)">Обработана</button>
                    </div>

                    <p class="text-secondary mt-3">
                        Мерчант:
                        <span class="float-right">
                            {{ claim.merchant.display_name ? claim.merchant.display_name : 'N/A' }}
                        </span>
                    </p>
                    <p class="text-secondary mt-3">
                        Автор:<span class="float-right">{{ claim.userName ? claim.userName : 'N/A' }}</span>
                    </p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="shadow p-3 height-100">
                    <h2>{{ claim.productsQty }} ед. товара</h2>
                </div>
            </div>
        </div>

        <v-tabs :current="nav.currentTab" :items="nav.tabs" @nav="tab => nav.currentTab = tab"></v-tabs>
        <offers-tab
                v-if="nav.currentTab === 'offers'"
                :claim="claim"
                @onSave="onChange"
        ></offers-tab>
    </layout-main>
</template>

<script>

    import {mapGetters} from 'vuex';

    import VTabs from '../../../../components/tabs/tabs.vue';
    import OffersTab from './components/offers-tab.vue';
    import Services from '../../../../../scripts/services/services';
    import modalMixin from '../../../../mixins/modal';

    export default {
    components: {
        VTabs,

        OffersTab,
    },
    mixins: [modalMixin],
    props: {
        iClaim: {},
        claimStatuses: {},
    },
    data() {
        return {
            claim: this.iClaim,

            nav: {
                currentTab: 'offers',
                tabs: [
                    {value: 'offers', text: 'Предложения мерчанта'},
                ]
            }
        };
    },
    methods: {
        statusName(statusId) {
            return this.claimStatuses[statusId] || 'N/A';
        },
        statusClass(statusId) {
            switch (statusId) {
                case 1: return 'badge-info';
                case 2: return 'badge-secondary';
                case 3: return 'badge-primary';
                case 4: return 'badge-success';
                case 5: return 'badge-warning';
                default: return 'badge-light';
            }
        },
        isStatus(statusId) {
            return this.claim.status === statusId;
        },
        changeClaimStatus(statusId) {
            let errorMessage = 'Ошибка при изменении статуса заявки.';

            Services.net().put(this.getRoute('priceChangeClaims.changeStatus', {id: this.claim.id}), null,
                {'status': statusId}).then(data => {
                if (data.result === 'ok') {
                    this.onChange(data);
                } else {
                    this.showMessageBox({title: 'Ошибка', text: errorMessage + ' ' + data.error});
                }
            }, () => {
                this.showMessageBox({title: 'Ошибка', text: errorMessage});
            });
        },
        onChange(data) {
            this.claim = data.claim;
        },
    },
    computed: {
        ...mapGetters(['getRoute']),
        isNewStatus() {
            return this.isStatus(1);
        },
        isWorkStatus() {
            return this.isStatus(2);
        },
    },
};
</script>

<style scoped>
    .claim-header {
        min-height: 200px;
    }
    .claim-header > div {
        padding: 16px 0 16px 16px;
    }
    .claim-header img {
        max-height: calc( 200px - 16px * 2 );
    }
    .claim-header p {
        margin: 0;
        padding: 0;
    }
    .height-100 {
        min-height: 100%;
        height: 100%;
    }
</style>
