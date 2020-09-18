<template>
    <layout-main>
        <div class="card mt-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-8 col-sm-6">
                        <input v-model="search_phrase"
                               @input="search(search_phrase)"
                               class="form-control form-control-lg"
                               type="text"
                               placeholder="Поиск атрибута...">
                    </div>
                    <div class="col-lg-4 col-sm-6 ml-auto">
                        <a :href="getRoute('products.properties.create')"
                           class="btn btn-lg btn-dark float-right mr-4">
                            <fa-icon icon="plus"/> Создать атрибут
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="container mt-5">
            <div class="row">
                <p v-if="properties.length === 0" class="mx-auto lead text-muted">
                    <fa-icon icon="search"/> Поиск не принёс результатов
                </p>
                <div v-else v-for="character in headers" class="col-lg-3 col-sm-6">
                    <h5>{{ character }}</h5>
                    <ul class="list-unstyled">
                        <li v-for="prop in properties"
                            v-if="prop.name[0].toUpperCase() === character">
                            <small>-
                                <a :href="getRoute('products.properties.detail', {id: prop.code})"
                                   class="text-dark">
                                    {{ prop.name }}
                                </a>
                            </small>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </layout-main>
</template>

<script>
export default {
    props: ['iProperties'],
    data() {
        return {
            properties: this.iProperties,
            headers: [],
            search_phrase: '',
        }
    },
    methods: {
        initiateHeaders() {
            this.headers = [];
            this.properties.forEach(item => {
                if (!this.headers.includes(item.name[0].toUpperCase())) {
                    this.headers.push(item.name[0].toUpperCase());
                }
            });

            this.headers.sort((a, b) => {
                if (a > b) return 1;
                if (a === b) return 0;
                if (a < b) return -1;
            })
        },
        search: async function(string) {
            if (string.length === 0) {
                this.properties = this.iProperties
                await this.$nextTick();
                this.initiateHeaders();
                return
            }
            let regexp = new RegExp(string, 'i');

            this.properties = [];
            this.iProperties.forEach(item => {
                if (regexp.test(item.name)) {
                    this.properties.push(item);
                }
            });
            await this.$nextTick();
            this.initiateHeaders();
        }
    },
    created() {
        this.initiateHeaders();
    }
}
</script>

<style scoped>

</style>