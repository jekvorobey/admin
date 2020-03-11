<template>
    <layout-main>
        <div class="form-group">
            <label for="level">Уровень</label>
            <select v-model="level_id" @change="loadLevel" class="form-control" id="level">
                <option :value="null">Выберите уровень</option>
                <option v-for="level in levels" :value="level.id">{{ level.name }}</option>
            </select>
        </div>
        <div v-if="level">
            <hr/>
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="name">Название</label>
                    <input class="form-control" id="name" v-model="level.name">
                </div>
                <div class="form-group col-md-2">
                    <label for="sort">Сортировка</label>
                    <input class="form-control" id="sort" v-model="level.sort">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-3">
                    <label for="referral_count">Кол-во рефералов для перехода на уровень</label>
                    <input class="form-control" id="referral_count" v-model="level.referral_count">
                </div>
                <div class="form-group col-md-3">
                    <label for="order_personal_sum">Сумма заказов (личных) для перехода на уровень</label>
                    <input class="form-control" id="order_personal_sum" v-model="level.order_personal_sum">
                </div>
                <div class="form-group col-md-3">
                    <label for="order_personal_count">Кол-во заказов (личных) для перехода на уровень</label>
                    <input class="form-control" id="order_personal_count" v-model="level.order_personal_count">
                </div>
                <div class="form-group col-md-3">
                    <label for="order_referral_sum">Сумма заказов (реферальных) для перехода на уровень</label>
                    <input class="form-control" id="order_referral_sum" v-model="level.order_referral_sum">
                </div>
            </div>
            <button class="btn btn-success" @click="putLevel">Сохранить</button>

            <hr/>

            <div class="form-row">
                <div class="form-group col-md-3">
                    <label for="percent_x">Acquisition business</label>
                    <input class="form-control" id="percent_x" v-model="level.commission.percent_x">
                </div>
                <div class="form-group col-md-3">
                    <label for="percent_y">Ongoing business</label>
                    <input class="form-control" id="percent_y" v-model="level.commission.percent_y">
                </div>
                <div class="form-group col-md-3">
                    <label for="percent_t">Promo-business - 1</label>
                    <input class="form-control" id="percent_t" v-model="level.commission.percent_t">
                </div>
                <div class="form-group col-md-3">
                    <label for="percent_p">Promo-business - 2</label>
                    <input class="form-control" id="percent_p" v-model="level.commission.percent_p">
                </div>
                <div class="form-group col-md-3">
                    <label for="percent_z">Promo-business - 3</label>
                    <input class="form-control" id="percent_z" v-model="level.commission.percent_z">
                </div>
            </div>
            <button class="btn btn-success" @click="putCommission">Сохранить</button>

            <hr/>

            <table class="table">
            <thead>
                <tr>
                    <th>Суммировать с другими типами</th>
                    <th>Коэффициент</th>
                    <th>Товар</th>
                    <th>Бренд</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="commission in level.special_commissions">
                    <td>
                        <input type="checkbox" v-model="commission.is_sum">
                    </td>
                    <td>
                        <input class="form-control form-control-sm" v-model="commission.coefficient">
                    </td>
                    <td>
                        <input class="form-control form-control-sm" v-model="commission.product_id">
                    </td>
                    <td>
                        <input class="form-control form-control-sm" v-model="commission.brand_id">
                    </td>
                    <td>
                        <button class="btn btn-success btn-sm" @click="putSpecialCommission(commission)"><fa-icon icon="save"/></button>
                        <v-delete-button btn-class="btn-danger btn-sm" @delete="removeSpecialCommission(commission)"/>
                    </td>
                </tr>
                <tr>
                    <td>
                        <input type="checkbox" v-model="newSpecialCommission.is_sum">
                    </td>
                    <td>
                        <input class="form-control form-control-sm" v-model="newSpecialCommission.coefficient">
                    </td>
                    <td>
                        <input class="form-control form-control-sm" v-model="newSpecialCommission.product_id">
                    </td>
                    <td>
                        <input class="form-control form-control-sm" v-model="newSpecialCommission.brand_id">
                    </td>
                    <td>
                        <button class="btn btn-success btn-sm" @click="putSpecialCommission(newSpecialCommission)">
                            <fa-icon icon="save"/>
                        </button>
                    </td>
                </tr>
            </tbody>
            </table>
        </div>
    </layout-main>
</template>

<script>

import Services from '../../../../../scripts/services/services.js';
import VDeleteButton from '../../../../components/controls/VDeleteButton/VDeleteButton.vue';

export default {
    components: {VDeleteButton},
    props: ['levels'],
    data() {
        return {
            level_id: null,
            level: null,
            newSpecialCommission: {
                is_sum: false,
                coefficient: '',
                product_id: '',
                brand_id: '',
            }
        };
    },
    methods: {
        loadLevel() {
            this.level = null;
            if (!this.level_id) {
                return;
            }
            Services.showLoader();
            Services.net().post(this.getRoute('referral.levels.detail', {level_id: this.level_id})).then(data => {
                this.level = data.level;
                Services.hideLoader();
            });
        },
        putLevel() {
            if (!this.level_id) {
                return;
            }
            Services.showLoader();
            Services.net().put(this.getRoute('referral.levels.save', {level_id: this.level_id}), {}, {
                name: this.level.name,
                sort: this.level.sort,
                referral_count: this.level.referral_count,
                order_personal_sum: this.level.order_personal_sum,
                order_personal_count: this.level.order_personal_count,
                order_referral_sum: this.level.order_referral_sum,
            }).then(data => {
                this.level = data.level;
            }).finally(() => {
                Services.hideLoader();
            });
        },
        putCommission() {
            if (!this.level_id) {
                return;
            }
            Services.showLoader();
            Services.net().put(this.getRoute('referral.levels.commission.save', {level_id: this.level_id}), {}, {
                percent_x: this.level.commission.percent_x,
                percent_y: this.level.commission.percent_y,
                percent_t: this.level.commission.percent_t,
                percent_p: this.level.commission.percent_p,
                percent_z: this.level.commission.percent_z,
            }).then(data => {
                this.level = data.level;
            }).finally(() => {
                Services.hideLoader();
            });
        },
        putSpecialCommission(commission) {
            if (!this.level_id) {
                return;
            }
            Services.showLoader();
            Services.net().put(this.getRoute('referral.levels.special-commission.save', {level_id: this.level_id}), {}, {
                is_sum: commission.is_sum,
                coefficient: commission.coefficient,
                product_id: commission.product_id,
                brand_id: commission.brand_id,
            }).then(data => {
                this.level = data.level;
                this.newSpecialCommission = {
                    is_sum: false,
                    coefficient: '',
                    product_id: '',
                    brand_id: '',
                };
            }).finally(() => {
                Services.hideLoader();
            });
        },
        removeSpecialCommission(commission) {
            if (!this.level_id) {
                return;
            }
            Services.showLoader();
            Services.net().delete(this.getRoute('referral.levels.special-commission.remove', {level_id: this.level_id}), {
                product_id: commission.product_id,
                brand_id: commission.brand_id,
            }).then(data => {
                this.level = data.level;
            }).finally(() => {
                Services.hideLoader();
            });
        },
    }
};
</script>
