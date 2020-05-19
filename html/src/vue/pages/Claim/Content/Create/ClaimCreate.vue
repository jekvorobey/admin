<template>
    <layout-main back>
        <div class="row">
            <div class="col-lg-5 col-md-7 col-sm-12">
                <claim-form
                        :merchant-options="options.merchantOptions"
                        :content-claim-option="options.typeOptions"
                        :photo-type-options="options.unpackOptions"
                        :no-unpack="options.noUnpack"
                        @onSave="onCreateClaim"
                ></claim-form>
            </div>
        </div>
    </layout-main>
</template>

<script>

    import ClaimForm from '../components/forms/claim-main-form.vue';
    import Services from "../../../../../scripts/services/services";

    export default {
        components: {
            ClaimForm,
        },

        props: {
            options: {}
        },

        methods: {
            onCreateClaim(result) {
                if (result) {
                    Services.msg("Заявка сохранена!");
                    window.location.href = this.getRoute('contentClaims.detail', {id: result});
                }
            }
        }
    }
</script>