<template>
    <div>
        <div class="form-group">
            <div class="form-check form-check-inline">
                <input v-model="mediaType"
                       class="form-check-input"
                       type="radio"
                       id="imageOption"
                       :value="publicEventMediaTypes.image" />
                <label class="form-check-label" for="imageOption">Картинка</label>
            </div>
            <div class="form-check form-check-inline">
                <input v-model="mediaType"
                       class="form-check-input"
                       type="radio"
                       id="videoOption"
                       :value="publicEventMediaTypes.video" />
                <label class="form-check-label" for="videoOption">Видео</label>
            </div>
            <div class="form-check form-check-inline">
                <input v-model="mediaType"
                       class="form-check-input"
                       type="radio"
                       id="youtubeOption"
                       :value="publicEventMediaTypes.youtube" />
                <label class="form-check-label" for="youtubeOption">Ролик на Youtube</label>
            </div>
        </div>
        <template v-if="mediaType !== publicEventMediaTypes.youtube">
            <file-input destination="publicEvent" @uploaded="onUpload"/>
            <template v-if="value">
                <img v-if="mediaType === publicEventMediaTypes.image" :src="imageUrl(value,0, 0, 'orig')" alt="Uploaded image">
                <a v-else :href="fileUrl(value)" target="_blank">Файл загружен</a>
            </template>
        </template>
        <template v-else>
            <v-input v-model="youtubeValue">Код ролика или ссылка "Поделиться"</v-input>
            <div v-if="value" class="embed-responsive embed-responsive-16by9 ">
                <iframe
                        width="424"
                        height="238"
                        class="embed-responsive-item"
                        :src="youtubeUrl(value)"
                        allowfullscreen>
                </iframe>
            </div>
        </template>
        <div class="form-group mt-3">
            <button @click="onAccept" class="btn btn-dark" :disabled="!value">Сохранить</button>
        </div>
    </div>
</template>

<script>
    import mediaMixin from '../../../../../mixins/media';
    import FileInput from '../../../../../components/controls/FileInput/FileInput.vue';
    import VInput from '../../../../../components/controls/VInput/VInput.vue';

    export default {
        mixins: [mediaMixin],
        components: {
            FileInput,
            VInput
        },
        data() {
            return {
                mediaType: null,
                value: null,
                youtubeValue: null,
            };
        },
        methods: {
            onUpload(file) {
                this.value = file.id
            },
            onAccept() {
                this.$emit('onSave', {
                    type: this.mediaType,
                    value: this.value,
                });
            }
        },
        watch: {
            mediaType() {
                this.value = null;
            },
            youtubeValue(value) {
                if (/https:\/\/.*\/.+/.test(value)) {
                    let match = /https:\/\/.*\/(?<code>.+)/.exec(value)
                    this.value = match.groups.code;
                } else {
                    this.value = value;
                }
            }
        },
        created() {
            this.mediaType = this.publicEventMediaTypes.image;
        }
    }
</script>

<style scoped>

</style>