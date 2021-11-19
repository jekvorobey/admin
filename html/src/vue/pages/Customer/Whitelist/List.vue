<template>
    <layout-main>
        <div class="card">
            <div class="card-body">
                <file-input v-if="canUpdate(blocks.clients)" @uploaded="(data) => onFileUpload(data)"
                            class="mb-3"
                            destination="whitelist"
                            label="Загрузить вайтлист"
                ></file-input>
                <button class="btn btn-secondary" @click="downloadWhitelistLink()">
                    Выгрузить вайтлист
                </button>
            </div>
        </div>
    </layout-main>
</template>

<script>

import FileInput from '../../../components/controls/FileInput/FileInput.vue';
import Services from "../../../../scripts/services/services";

export default {
    components: {FileInput},
    methods: {
        onFileUpload(data) {
            Services.showLoader();

            Services.net().post(this.getRoute('customers.whitelist.import'), {
                file_id: data.id,
            }).then((data) => {
                Services.msg('Импорт выполнен успешно');
            }).finally(() => {
                Services.hideLoader();
            });
        },
        downloadWhitelistLink() {
            window.open(this.getRoute('customers.whitelist.export'));
        },
    },
};
</script>
