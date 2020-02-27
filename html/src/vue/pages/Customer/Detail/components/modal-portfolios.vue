<template>
    <b-modal id="modal-portfolios" title="Редактирование портфолио" hide-footer>
        <template v-slot:default="{close}">

            <table>
                <thead>
                    <tr>
                        <th>Название</th>
                        <th>Ссылка</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(portfolio, i) in portfolios">
                        <td><input class="form-control form-control-sm" v-model="portfolio.name"/></td>
                        <td><input class="form-control form-control-sm" v-model="portfolio.link"/></td>
                        <td>
                            <button class="btn btn-danger btn-sm" @click="deletePortfolio(i)">
                                <fa-icon icon="trash-alt"/>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td><input class="form-control form-control-sm" v-model="portfolioNew.name"/></td>
                        <td><input class="form-control form-control-sm" v-model="portfolioNew.link"/></td>
                        <td>
                            <button class="btn btn-success btn-sm" @click="addPortfolio()">
                                <fa-icon icon="plus"/>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="float-right mt-3">
                <b-button @click="close()" variant="outline-primary">Отмена</b-button>
                <button class="btn btn-info" @click="savePortfolios">Сохранить</button>
            </div>
        </template>
    </b-modal>
</template>

<script>
import Services from '../../../../../scripts/services/services.js';
import { mapGetters } from 'vuex';

export default {
    name: 'modal-portfolios',
    props: ['model', 'customerId'],
    data() {
        return {
            portfolios: JSON.parse(JSON.stringify(this.model)),
            portfolioNew: {
                link: "",
                name: "",
            }
        }
    },
    computed: {
        ...mapGetters(['getRoute']),
    },
    methods: {
        savePortfolios() {
            Services.showLoader();
            Services.net().put(this.getRoute('customers.detail.portfolio.save', {id: this.customerId}), null, {
                portfolios: this.portfolios
            }).then(data => {
                this.$emit('update:model', JSON.parse(JSON.stringify(this.portfolios)));
                Services.hideLoader();
                this.$bvModal.hide("modal-portfolios");
            });
        },
        addPortfolio() {
            if (!this.portfolioNew.link || !this.portfolioNew.name) {
                Services.msg("Заполните все поля", "danger");
                return;
            }
            this.$set(this.portfolios, this.portfolios.length, JSON.parse(JSON.stringify(this.portfolioNew)));
            this.portfolioNew.link = "";
            this.portfolioNew.name = "";
        },
        deletePortfolio(index) {
            this.$delete(this.portfolios, index);
        },
    }
};
</script>

<style scoped>

</style>