<template>
    <div>
        <b-input-group class="mb-2">
            <b-form-textarea rows="5" v-model="productCodes" placeholder="Введите артикулы" />
        </b-input-group>

        <div v-if="showReport && report.length > 0" class="report-table__pane mb-3">
            <table class="report-table">
                <tr v-for="(item, i) in report" :key="i" :class="[ item.variant ]">
                    <td style="width: 20%" :variant="item.variant">
                        {{ mode === modes.OFFER_ID ? 'Офер' : 'Артикул' }} {{ item.vendorCode }}
                    </td>
                    <td :variant="item.variant">{{ item.status }}</td>
                </tr>
            </table>
        </div>

        <div class="d-flex">
            <b-button v-on:click="changeMode" class="mr-2">
                {{ mode === modes.OFFER_ID ? 'Поиск по оферам' : 'Поиск по артикулам' }}
            </b-button>
            <b-button v-on:click="onLoadClick" class="mr-2" variant="info">Добавить</b-button>
            <b-button v-on:click="$refs.fileInput.$refs.input.click()" variant="info">Добавить из файла</b-button>

            <b-form-file
                ref="fileInput"
                v-model="file"
                class="d-none"
                accept=".csv"
                plain
                placeholder="Добавить из файла"
            />
        </div>
    </div>
</template>

<script>
import _chunk from 'lodash/chunk';
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
            file: null,
            mode: mode.VENDOR_CODE,

            modes: mode,
        };
    },

    watch: {
        file() {
            if (this.file) {
                const reader = new FileReader();

                reader.onload = async (event) => {
                    let productCodes = event.target.result.split("\n").filter(code => !!code);

                    if (productCodes.length > 0) {
                        await this.load(productCodes);
                        this.file = null;
                    }
                };

                reader.readAsText(this.file);
            }
        }
    },

    methods: {
        onLoadClick() {
            const productCodes = this.productCodes.split("\n").filter(code => !!code);

            if (productCodes.length > 0) {
                this.load(productCodes);
            }
        },

        async load(productCodes) {
            const loadedIds = [];

            if (productCodes.length > 0) {
                Services.showLoader();
                this.report = [];
            }

            const params = {
                filter: {}
            };

            try {
                let offers = [];
                const parts = _chunk(productCodes, 500);

                for (const codes of parts) {
                    if (this.mode === mode.OFFER_ID) {
                        params.filter.id = codes;
                    } else {
                        params.filter.vendor_code = codes;
                    }

                    let responseOffers = await Services.net().post(
                        this.getRoute('offers.find'),
                        {},
                        params,
                    );

                    offers = [
                        ...offers,
                        ...responseOffers
                    ];

                    await this.sleep();
                }

                const finded = offers.map(item => {
                    if (this.mode === mode.VENDOR_CODE) {
                        return item.vendorCode;
                    }

                    return item.id;
                });

                productCodes.forEach(codeOrId => {
                    if (finded.includes(codeOrId)) {
                        const offer = offers.find(offer => {
                            if (this.mode === mode.VENDOR_CODE) {
                                return offer.vendorCode === codeOrId;
                            }

                            return offer.id === codeOrId;
                        });

                        if (!this.loadedProducts.includes(offer.id)) {
                            loadedIds.push(offer.id);

                            this.report.push({
                                vendorCode: codeOrId,
                                variant: 'success',
                                status: 'Добавлен',
                            });
                        } else {
                            this.report.push({
                                vendorCode: codeOrId,
                                variant: 'warning',
                                status: 'Повторный'
                            });
                        }
                    } else {
                        this.report.push({
                            vendorCode: codeOrId,
                            variant: 'danger',
                            status: 'Не найден'
                        });
                    }
                });

                if (loadedIds.length > 0) {
                    this.$emit('load', loadedIds);
                }

                Services.hideLoader();

                this.productCodes = '';
            } catch (error) {
                console.error(error);
                Services.hideLoader();
            }
        },

        changeMode() {
            if (this.mode === this.modes.VENDOR_CODE) {
                this.mode = this.modes.OFFER_ID;
            } else {
                this.mode = this.modes.VENDOR_CODE;
            }
        },

        sleep(timeout = 100) {
            return new Promise(resolve => {
                setTimeout(resolve, timeout);
            });
        }
    },
};
</script>

<style scoped>
.report-table__pane {
    max-height: 300px;
    overflow-y: scroll;
}

.report-table td {
    border: 1px solid #000000;
}

.report-table tr.success td {
    background-color: #c3e6cb;
}

.report-table tr.warning td {
    background-color: #ffeeba;
}

.report-table tr.danger td {
    background-color: #f5c6cb;
}

</style>
