<template>
  <layout-main>
    <div class="d-flex justify-content-between mt-3 mb-3">
      <button class="btn btn-success" @click="createReason">Создать причину отмены</button>
    </div>
    <table class="table">
      <thead>
      <tr>
        <th>ID</th>
        <th>Причина отмены</th>
      </tr>
      </thead>
      <tbody>
      <tr v-for="reason in orderReturnReasons">
        <td>{{ reason.id }}</td>
        <td>{{ reason.text }}</td>
        <td>
          <v-delete-button @delete="() => deleteOrderReturnReason(reason.id)" class="float-right ml-1"/>
          <button class="btn btn-warning float-right" @click="editReason(reason)">
            <fa-icon icon="edit"></fa-icon>
          </button>
        </td>
      </tr>
      </tbody>
    </table>
    <div>
      <b-pagination
          v-if="numPages !== 1"
          v-model="page"
          :total-rows="total"
          :per-page="pageSize"
          :hide-goto-end-buttons="numPages < 10"
          class="mt-3 float-right"
      ></b-pagination>
    </div>
    <transition name="modal">
      <modal :close="closeModal" v-if="isModalOpen('ReturnReasonFormModal')">
        <div slot="header">
          Причина отмены
        </div>
        <div slot="body">
          <div class="form-group">
            <v-input v-model="$v.form.text.$model" :error="errorText">Причина*</v-input>
          </div>
          <div class="form-group">
            <button @click="onSave" type="button" class="btn btn-primary">Сохранить</button>
            <button @click="onCancel" type="button" class="btn btn-secondary">Отмена</button>
          </div>
        </div>
      </modal>
    </transition>
  </layout-main>
</template>

<script>
    import withQuery from 'with-query';
    import qs from 'qs';

    import {mapActions, mapGetters} from 'vuex';

    import {
      ACT_DELETE_RETURN_REASON,
      ACT_LOAD_PAGE,
      ACT_SAVE_RETURN_REASON,
      GET_LIST,
      GET_NUM_PAGES,
      GET_PAGE_NUMBER,
      GET_PAGE_SIZE,
      GET_TOTAL,
      NAMESPACE,
      SET_PAGE,
    } from '../../../../store/modules/orderReturnReasons';

    import modalMixin from '../../../../mixins/modal';
    import mediaMixin from '../../../../mixins/media';
    import {validationMixin} from 'vuelidate';
    import {required} from 'vuelidate/lib/validators';

    import Modal from '../../../../components/controls/modal/modal.vue';
    import VInput from '../../../../components/controls/VInput/VInput.vue';
    import VDeleteButton from '../../../../components/controls/VDeleteButton/VDeleteButton.vue';
    import Services from "../../../../../scripts/services/services";

    export default {
        mixins: [
          modalMixin,
          mediaMixin,
          validationMixin,
        ],
        components: {
          Modal,
          VInput,
          VDeleteButton
        },
        props: {
          iOrderReturnReasons: {},
          iTotal: {},
          iCurrentPage: {},
        },
        data() {
          this.$store.commit(`${NAMESPACE}/${SET_PAGE}`, {
            list: this.iOrderReturnReasons,
            total: this.iTotal,
            page: this.iCurrentPage
          });

          return {
            editReasonId: null,
            form: {
              text: null,
            },
            massSelectionType: 'orderReturnReasons',
          };
        },
        validations: {
          form: {
            text: {required},
          }
        },
        methods: {
          ...mapActions(NAMESPACE, [
            ACT_LOAD_PAGE,
            ACT_SAVE_RETURN_REASON,
            ACT_DELETE_RETURN_REASON,
          ]),
          loadPage(page) {
            history.pushState(null, null, location.origin + location.pathname + withQuery('', {
              page: page,
            }));

            Services.showLoader();
            this[ACT_LOAD_PAGE]({page})
                .finally(() => {
                  Services.hideLoader();
                });
          },

          createReason() {
            this.$v.form.$reset();
            this.editReasonId = null;
            this.form.text = null;
            this.openModal('ReturnReasonFormModal');
          },

          editReason(returnReason) {
            this.$v.form.$reset();
            this.editReasonId = returnReason.id;
            this.form.text = returnReason.text;
            this.openModal('ReturnReasonFormModal');
          },
          onSave() {
            this.$v.$touch();
            if (this.$v.$invalid) {
              return;
            }
            Services.showLoader();
            this[ACT_SAVE_RETURN_REASON]({
              id: this.editReasonId,
              orderReturnReason: this.form
            }).then(() => {
              return this[ACT_LOAD_PAGE]({page: this.page});
            }).finally(() => {
              this.closeModal();
              Services.hideLoader();
            });
          },
          onCancel() {
            this.closeModal();
          },
          deleteOrderReturnReason(id) {
            Services.showLoader();
            this[ACT_DELETE_RETURN_REASON]({id})
                .then(() => {
                  return this[ACT_LOAD_PAGE]({page: this.page});
                })
                .finally(() => {
                  Services.hideLoader();
                });
          },
        },
        created() {
          window.onpopstate = () => {
            let query = qs.parse(document.location.search.substr(1));
            if (query.page) {
              this.page = query.page;
            }
          };
        },
        computed: {
          ...mapGetters(NAMESPACE, {
            GET_PAGE_NUMBER,
            total: GET_TOTAL,
            pageSize: GET_PAGE_SIZE,
            numPages: GET_NUM_PAGES,
            orderReturnReasons: GET_LIST,
          }),
          page: {
            get: function () {
              return this.GET_PAGE_NUMBER;
            },
            set: function (page) {
              this.loadPage(page);
            }
          },

          errorText() {
            if (this.$v.form.text.$dirty) {
              if (!this.$v.form.text.required) return "Обязательное поле!";
            }
          },
        }
    };
</script>

<style scoped>
  .table thead {
    border-top: 1px solid #dee2e6;
  }
  .table thead th {
    border-bottom: inherit;
  }
</style>
