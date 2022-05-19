<template>
    <b-modal id="redirect-edit-modal" hide-footer ref="modal" size="lg">
        <div slot="modal-title">
            <strong v-if="mode === 'create'">Создать новый редирект</strong>
            <strong v-else-if="mode === 'edit'">Редактировать редирект</strong>
        </div>
        <div class="card">
            <div class="card-body">
                <b-row class="mb-2">
                    <b-col cols="4">
                        <label> Результат</label>
                    </b-col>
                    <b-col cols="8">
                        <v-input
                            v-model="to"
                            class="mb-2"
                            :error="reqErrors.to"
                        />
                    </b-col>
                </b-row>
                <b-row class="mb-2">
                    <b-col cols="4">
                        <label>Источник</label>
                    </b-col>
                    <b-col cols="8">
                        <v-input
                            ref="fromComponent"
                            v-model="from"
                            class="mb-0"
                            :error="reqErrors.from"
                        />
                    </b-col>
                </b-row>
                <b-row class="mb-2">
                    <b-col cols="4" />
                    <b-col cols="8">
                        <div class="mb-2">
                            <b-button size="sm" @click="generateShortUrl">Сгенерировать</b-button>
                            <b-button size="sm" @click="copyFrom">Скопировать</b-button>
                        </div>
                    </b-col>
                </b-row>
            </div>
        </div>
        <div class="mt-3">
            <button
                class="btn btn-success"
                :disabled="invalid"
                @click="save"
            >
                Сохранить
            </button>
            <button
                class="btn btn-outline-danger"
                @click="closeModal"
            >
                Отмена
            </button>
        </div>
    </b-modal>
</template>

<script>
import VInput from '../../../../components/controls/VInput/VInput.vue';
import Services from "../../../../../scripts/services/services";
import axios from 'axios';
import Helpers from '../../../../../scripts/helpers';

export default {
    name: "redirect-edit-modal.vue",
    components: {
        VInput
    },
    props: {
        redirect: null,
        options: {
            type: Object,
            default() {
                return {
                    host: ''
                };
            },
        },
    },
    data() {
        return {
            id: null,
            from: '',
            to: '',
            invalid: false,
            mode: 'create',
            reqErrors: this.emptyErrors()
        }
    },
    methods: {
        save() {
            Services.showLoader();
            // this.addSlashes()
            const redirectData = {
                from: this.from,
                to: this.to,
            }
            const savePromise = this.mode === 'edit'
                ? this.updateRedirect({id: this.id, ...redirectData})
                : this.createRedirect(redirectData);

            this.reqErrors = this.emptyErrors();
            savePromise.then(data => {
                this.$emit('saved');
                this.closeModal();
                this.from = ''
                this.to = ''
                this.id = ''
            })
                .catch(error => {
                    const responseErrors = error.response.data.errors;
                    if (responseErrors) {
                        for (let key in responseErrors) {
                            if (responseErrors.hasOwnProperty(key) && this.reqErrors.hasOwnProperty(key)) {
                                this.reqErrors[key] = responseErrors[key][0]
                            }
                        }
                    }

                    if (error.response.data.message) {
                        Services.msg(error.response.data.message, 'danger');
                    }
                })
                .finally(() => {
                    Services.hideLoader();
                });
        },
        addSlashes() {
            this.from = Helpers.addSlash(this.from)
            this.to = Helpers.addSlash(this.to)
        },
        closeModal() {
            this.$bvModal.hide('redirect-edit-modal');
        },
        createRedirect(data) {
            return axios.post(this.getRoute('redirect.create'), data);
        },
        updateRedirect(data) {
            return axios.put(this.getRoute('redirect.update', {id: data.id}), data);
        },
        emptyErrors() {
            return {
                from: '',
                to: ''
            }
        },

        async copyFrom() {
            const oldValue = this.from;

            if (this.options.host.length > 0 && this.from.indexOf(this.options.host) === -1) {
                this.from = this.options.host + this.from;
            }

            await this.$nextTick();
            this.$refs.fromComponent.copy();
            this.from = oldValue;
        },

        async generateShortUrl() {
            let tryCounts = 1;
            let isUnique = false;
            let shortString = '/' + Helpers.getRandomString(6);

            while (isUnique === false && tryCounts <= 10) {
                const { redirects } = await Services.net().get(this.getRoute('redirect.page'), {
                    filter: {
                        from: shortString,
                    }
                });

                if (redirects.length === 0) {
                    isUnique = true;
                } else {
                    tryCounts++;
                    shortString = '/' + Helpers.getRandomString(6);
                }
            }

            if (!isUnique) {
                throw new Error('Не удалось сгенерировать уникальную ссылку');
            }

            this.from = shortString;
        },
    },
    watch: {
        redirect(value) {
            this.mode = value ? 'edit' : 'create'
            if (value) {
                this.from = value.from
                this.to = value.to
                this.id = +value.id
            } else {
                this.from = ''
                this.to = ''
                this.id = null
            }
        }
    }
}
</script>
