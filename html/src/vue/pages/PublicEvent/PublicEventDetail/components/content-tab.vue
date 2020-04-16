<template>
    <div>
        <h3 class="mt-3">Изображения</h3>
        <div class="row">
            <div class="col">
                <div class="media-container d-flex flex-wrap align-items-stretch justify-content-start">
                    <v-media
                            :media-object="mainMedia"
                            @onEdit="() => startUploadMedia(mainMedia.id, mainMedia.collection)"
                            @onDelete="() => onDeleteMedia(mainMedia.id)"
                    >Основное изображение</v-media>
                    <v-media
                            :media-object="catalogMedia"
                            @onEdit="() => startUploadMedia(catalogMedia.id, catalogMedia.collection)"
                            @onDelete="() => onDeleteMedia(catalogMedia.id)"
                    >Изображение для каталога</v-media>
                </div>
            </div>
            <div class="col">
                <div class="media-container d-flex flex-wrap align-items-stretch justify-content-start">
                    <v-media small
                            v-for="media in galleryMedia"
                            :key="media.id"
                            :media-object="media"
                             @onEdit="() => startUploadMedia(media.id, media.collection)"
                             @onDelete="() => onDeleteMedia(media.id)"
                    />

                    <div class="align-self-center">
                        <button
                                @click="() => startUploadMedia(null, publicEventMediaCollections.gallery)"
                                class="btn btn-light"
                        >Добавить</button>
                    </div>
                </div>
            </div>
        </div>
        <hr>

        <transition name="modal">
            <modal :close="closeModal" v-if="isModalOpen($const.mediaModal)">
                <div slot="header">
                    Загрузка файла
                </div>
                <div slot="body">
                    <media-form @onSave="finishUploadMedia"/>
                </div>
            </modal>
        </transition>
    </div>
</template>

<script>
    import {
        NAMESPACE,
        ACT_SAVE_EVENT_MEDIA,
        ACT_DELETE_EVENT_MEDIA,
    } from '../../../../store/modules/public-events';

    import modalMixin from '../../../../mixins/modal.js';
    import Media from '../../../../../scripts/media';

    import Modal from '../../../../components/controls/modal/modal.vue';
    import VMedia from './media.vue';
    import MediaForm from './forms/media-form.vue';
    import {mapActions} from "vuex";

    const $const = {
        mediaModal: 'mediaModal',
    };

    export default {
        mixins: [
            modalMixin
        ],
        components: {
            Modal,
            VMedia,
            MediaForm
        },
        props: {
            publicEvent: {},
        },
        data() {
            return {
                editMediaId: null,
                editMediaCollection: null,
            };
        },
        methods: {
            ...mapActions(NAMESPACE, {
                saveMedia: ACT_SAVE_EVENT_MEDIA,
                deleteMedia: ACT_DELETE_EVENT_MEDIA,
            }),
            async onDeleteMedia(mediaId) {
                await this.deleteMedia({
                    publicEventId: this.publicEvent.id,
                    mediaId
                });
                this.$emit('onChange');
            },
            startUploadMedia(mediaId, collection) {
                this.editMediaId = mediaId;
                this.editMediaCollection = collection;
                this.openModal($const.mediaModal);
            },
            async finishUploadMedia({type, value}) {
                if (value) {
                    await this.saveMedia({
                        publicEventId: this.publicEvent.id,
                        collection: this.editMediaCollection,
                        oldMedia: this.editMediaId,
                        type,
                        value
                    });
                    this.closeModal();
                    this.$emit('onChange');
                }
            }
        },
        computed: {
            $const() {
                return $const;
            },
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