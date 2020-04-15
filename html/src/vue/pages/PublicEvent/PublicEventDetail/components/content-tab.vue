<template>
    <div>
        <h3 class="mt-3">Изображения</h3>
        <div class="row">
            <div class="col">
                <div class="media-container d-flex flex-wrap align-items-stretch justify-content-start">
                    <v-media :media-object="mainMedia">Основное изображение</v-media>
                    <v-media :media-object="catalogMedia">Изображение для каталога</v-media>
                </div>
            </div>
            <div class="col">
                <div class="media-container d-flex flex-wrap align-items-stretch justify-content-start">
                    <v-media small
                            v-for="media in galleryMedia"
                            :key="media.id"
                            :media-object="media" />

                    <div class="align-self-center">
                        <button class="btn btn-light">Добавить</button>
                    </div>
                </div>
            </div>
        </div>
        <hr>
    </div>
</template>

<script>
    import Media from '../../../../../scripts/media';
    import VMedia from './media.vue';

    export default {
        components: {
            VMedia
        },
        props: {
            publicEvent: {},
        },
        methods: {
            onDeleteMedia() {

            },
            startUploadMedia() {

            },
        },
        computed: {
            allMedia() {
                return this.publicEvent.media.map(rawMedia => {
                    let url;
                    switch (rawMedia.type) {
                        case this.publicEventMediaTypes.image:
                            url = Media.compressed(rawMedia.value, 150, 150);
                            break;
                        case this.publicEventMediaTypes.youtube:
                            url = Media.video(rawMedia.value);
                            break;
                        default:
                            url = Media.file(rawMedia.value);
                            break;
                    }
                    return {
                        id: rawMedia.id,
                        collection: rawMedia.collection,
                        type: rawMedia.type,
                        value: rawMedia.value,
                        url
                    };
                });
            },
            emptyMedia() {
                return {
                    id: 0,
                    collection: this.publicEventMediaCollections.default,
                    type: this.publicEventMediaTypes.image,
                    value: 0,
                    url:Media.empty(150, 150)
                }
            },
            mainMedia() {
                let mainImages = this.allMedia.filter(media => media.collection === this.publicEventMediaCollections.detail);
                return mainImages.length > 0 ? mainImages[0] : this.emptyMedia;
            },
            catalogMedia() {
                let catalogImages = this.allMedia.filter(media => media.collection === this.publicEventMediaCollections.catalog);
                return catalogImages.length > 0 ? catalogImages[0] : this.emptyMedia;
            },
            galleryMedia() {
                return this.allMedia.filter(media => media.collection === this.publicEventMediaCollections.gallery);
            },
        }
    }
</script>

<style scoped>
    .media-container > div {
        padding: 16px;
    }
</style>