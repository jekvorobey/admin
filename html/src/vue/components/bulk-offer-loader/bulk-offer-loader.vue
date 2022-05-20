<template>
    <div>
        <b-input-group class="mb-2">
            <b-form-textarea rows="5" v-model="productCodes" placeholder="Введите артикулы" />
        </b-input-group>

        <b-table-simple v-if="showReport && report.length > 0" class="mt-3" small>
            <b-tbody>
                <b-tr v-for="(item, i) in report" :key="i">
                    <b-td style="width: 20%" :variant="item.variant">Артикул {{ item.vendorCode }}</b-td>
                    <b-td :variant="item.variant">{{ item.status }}</b-td>
                </b-tr>
            </b-tbody>
        </b-table-simple>

        <div class="d-flex">
            <b-button v-on:click="changeMode" class="mr-2">
                {{ mode === modes.OFFER_ID ? 'Поиск по оферам' : 'Поиск по артикулам' }}
            </b-button>
            <b-button v-on:click="load" variant="info">Добавить</b-button>
        </div>
    </div>
</template>

<script>
import Services from '../../../scripts/services/services';

const mode = Object.freeze({
    VENDOR_CODE: 'VENDOR_CODE',
    OFFER_ID: 'OFFER_ID'
});

export default {
    props: {
        loadedProducts: {
            type: Array,
            default() {
                return [];
            }
        },

        showReport: {
            type: Boolean,
            default: false,
        }
    },

    data() {
        return {
            productCodes: '',
            report: [],
            mode: mode.VENDOR_CODE,

            modes: mode,
        };
    },

    methods: {
        async load() {
            const productCodes = this.productCodes.split("\n");
            const loadedIds = [];

            if (productCodes.length > 0) {
                Services.showLoader();
                this.report = [];
            }

            for (const productCode of productCodes) {
                try {
                    const params = {
                        page: 1,
                        filter: {}
                    };

                    if (this.mode === mode.OFFER_ID) {
                        params.filter.id = productCode;
                    } else {
                        params.filter.vendor_code = productCode;
                    }

                    const { offers } = await Services.net().get(
                        this.getRoute('offers.listPage'),
                        params,
                    );

                    if (offers && offers[0]) {
                        const offerId = offers[0].id;

                        if (!this.loadedProducts.includes(offerId)) {
                            loadedIds.push(offerId);

                            this.report.push({
                                vendorCode: productCode,
                                variant: 'success',
                                status: 'Добавлен',
                            });
                        } else {
                            this.report.push({
                                vendorCode: productCode,
                                variant: 'warning',
                                status: 'Повторный'
                            });
                        }
                    } else {
                        this.report.push({
                            vendorCode: productCode,
                            variant: 'danger',
                            status: 'Не найден'
                        });
                    }
                } catch (error) {
                    console.error(error);
                }
            }

            if (loadedIds.length > 0) {
                this.$emit('load', loadedIds);
            }

            Services.hideLoader();

            this.productCodes = '';
        },

        changeMode() {
            if (this.mode === this.modes.VENDOR_CODE) {
                this.mode = this.modes.OFFER_ID;
            } else {
                this.mode = this.modes.VENDOR_CODE;
            }
        }
    },
};
</script>
