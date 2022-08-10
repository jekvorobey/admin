<template>
    <div>
        <b-input-group class="mb-2">
            <b-form-textarea
                rows="5"
                v-model="productCodes"
                :placeholder="`Введите ${mode === modes.OFFER_ID ? 'идентификаторы предложений' : 'артикулы'}`"
            />
        </b-input-group>

        <div v-if="showReport && report.length > 0" class="report-table__pane mb-3">
            <table class="report-table">
                <tr v-for="(item, i) in report" :key="i" :class="[ item.variant ]">
                    <td style="width: 20%" :variant="item.variant">
                        {{ item.vendorCode }}
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
import { detect } from 'jschardet';
import Services from '../../../scripts/services/services';

export const mode = Object.freeze({
    VENDOR_CODE: 'VENDOR_CODE',
    OFFER_ID: 'OFFER_ID'
});

export const returnMode = Object.freeze({
    OFFER: 'OFFER',
    PRODUCT: 'PRODUCT',
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
        },

        chunkSize: {
            type: Number,
            default: 500,
        },

        loader: {
            type: Function,
            required: true,
        },

        returnMode: {
            type: String,
            default: returnMode.OFFER,
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
        async file() {
            if (this.file) {
                let readEncoding = 'UTF-8';

                const fileEncodingInfo = await this.detectFileEncoding(this.file);

                // Если в файле большая часть символов числа, то библиотеки неверное определяют кодировку
                // Подразумеваем, что если не UTF-8, то скорее всего Windows 1251
                if (fileEncodingInfo.encoding && fileEncodingInfo.encoding !== 'UTF-8') {
                    readEncoding = 'CP1251';
                }

                const reader = new FileReader();

                reader.onload = async (event) => {
                    let productCodes = event.target.result.split("\n")
                        .map(code => {
                            return code.replace(/\n/g, '').trim();
                        })
                        .filter(code => !!code);

                    if (productCodes.length > 0) {
                        await this.load(productCodes);
                        this.file = null;
                    }
                };

                reader.readAsText(this.file, readEncoding);
            }
        }
    },

    methods: {
        onLoadClick() {
            const productCodes = this.productCodes
                .split("\n")
                .filter(code => !!code)
                .map(code => {
                    if (this.mode === mode.OFFER_ID) {
                        return Number.parseInt(code);
                    }

                    return code;
                });

            if (productCodes.length > 0) {
                this.load(productCodes);
            }
        },

        async detectFileEncoding(file) {
            return new Promise(resolve => {
                const reader = new FileReader();

                reader.onload = (event) => {
                    resolve(detect(event.target.result));
                };

                reader.readAsBinaryString(file);
            });
        },

        async load(productCodes) {
            let loadedIds = [];

            if (productCodes.length > 0) {
                Services.showLoader();
                this.report = [];
            }

            try {
                let offers = [];
                const parts = _chunk(productCodes, this.chunkSize);

                for (const codes of parts) {
                    const loaderOffers = await this.loader(this.mode, codes);

                    offers = [
                        ...offers,
                        ...loaderOffers
                    ];

                    await this.sleep();
                }

                const found = offers.map(item => {
                    if (this.mode === mode.VENDOR_CODE) {
                        return item.vendorCode;
                    }

                    return item.id;
                });

                productCodes.forEach(codeOrId => {
                    if (found.includes(codeOrId)) {
                        const offer = offers.find(offer => {
                            if (this.mode === mode.VENDOR_CODE) {
                                return offer.vendorCode === codeOrId;
                            }

                            return offer.id === codeOrId;
                        });

                        if (!this.loadedProducts.includes(this.returnMode === returnMode.OFFER ? offer.id : offer.productId)) {
                            loadedIds.push(this.returnMode === returnMode.OFFER ? offer.id : offer.productId);

                            this.report.push({
                                vendorCode: `${this.mode === this.modes.OFFER_ID ? 'Офер' : 'Артикул'} ${codeOrId}`,
                                variant: 'success',
                                status: 'Добавлен',
                            });
                        } else {
                            this.report.push({
                                vendorCode: `${this.mode === this.modes.OFFER_ID ? 'Офер' : 'Артикул'} ${codeOrId}`,
                                variant: 'warning',
                                status: 'Повторный'
                            });
                        }
                    } else {
                        this.report.push({
                            vendorCode: `${this.mode === this.modes.OFFER_ID ? 'Офер' : 'Артикул'} ${codeOrId}`,
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
