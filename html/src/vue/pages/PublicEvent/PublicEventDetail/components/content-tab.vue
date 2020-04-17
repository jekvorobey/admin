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
        </div>
        <hr>

        <h3 class="mt-3">Описание</h3>
        <div class="row">
            <div class="col-lg-8 col-sm-12">
                <shadow-card title="Описание" :buttons="{onEdit:'pencil-alt'}" @onEdit="openModal($const.descriptionModal)">
                    {{ publicEvent.description }}
                </shadow-card>
            </div>
            <div class="col-lg-4 col-sm-12">
                <div class="media-container d-flex flex-wrap align-items-stretch justify-content-start">
                    <v-media
                            :media-object="descriptionMedia"
                            @onEdit="() => startUploadMedia(descriptionMedia.id, descriptionMedia.collection)"
                            @onDelete="() => onDeleteMedia(descriptionMedia.id)"
                    >Изображение для описания</v-media>
                </div>
            </div>
        </div>
        <hr>

        <h3 class="mt-3">Галлерея</h3>
        <div class="row">
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

        <h3 class="mt-3">Как это было</h3>
        <div class="row">
            <div class="col">
                <div class="media-container d-flex flex-wrap align-items-stretch justify-content-start">
                    <v-media small
                             v-for="media in historyMedia"
                             :key="media.id"
                             :media-object="media"
                             @onEdit="() => startUploadMedia(media.id, media.collection)"
                             @onDelete="() => onDeleteMedia(media.id)"
                    />

                    <div class="align-self-center">
                        <button
                                @click="() => startUploadMedia(null, publicEventMediaCollections.history)"
                                class="btn btn-light"
                        >Добавить</button>
                    </div>
                </div>
            </div>
        </div>

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

        <transition name="modal">
            <modal :close="closeModal" v-if="isModalOpen($const.descriptionModal)">
                <div slot="header">
                    Редактирование описание
                </div>
                <div slot="body">
                    <description-form :public-event="publicEvent" @onSave="onSaveDescripton"/>
                </div>
            </modal>
        </transition>
    </div>
</template>

<script>
    import {mapActions} from "vuex";

    import {ACT_DELETE_EVENT_MEDIA, ACT_SAVE_EVENT_MEDIA, NAMESPACE,} from '../../../../store/modules/public-events';

    import modalMixin from '../../../../mixins/modal.js';
    import Modal from '../../../../components/controls/modal/modal.vue';
    import ShadowCard from '../../../../components/shadow-card.vue';

    import VMedia from './media.vue';

    import MediaForm from './forms/media-form.vue';
    import DescriptionForm from './forms/description-form.vue';

    const $const = {
        mediaModal: 'mediaModal',
        descriptionModal: 'descriptionModal',
    };

    export default {
        mixins: [
            modalMixin
        ],
        components: {
            Modal,
            ShadowCard,
            VMedia,
            MediaForm,
            DescriptionForm
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
            },
            onSaveDescripton() {
                this.closeModal();
                this.$emit('onChange');
            },
            emptyMedia(collection) {
                return {
                    id: 0,
                    collection,
                    type: this.publicEventMediaTypes.image,
                    value: 0,
                }
            },
            imageOrEmpty(collection) {
                let images = this.allMedia.filter(media => media.collection === collection);
                return images.length > 0 ? images[0] : this.emptyMedia(collection);
            },
            images(collection) {
                return this.allMedia.filter(media => media.collection === collection);
            }
        },
        computed: {
            $const() {
                return $const;
            },
            allMedia() {
                return this.publicEvent.media.map(rawMedia => {
                    return {
                        id: rawMedia.id,
                        collection: rawMedia.collection,
                        type: rawMedia.type,
                        value: rawMedia.value,
                    };
                });
            },
            mainMedia() {
                return this.imageOrEmpty(this.publicEventMediaCollections.detail);
            },
            catalogMedia() {
                return this.imageOrEmpty(this.publicEventMediaCollections.catalog);
            },
            descriptionMedia() {
                return this.imageOrEmpty(this.publicEventMediaCollections.description);
            },
            galleryMedia() {
                return this.images(this.publicEventMediaCollections.gallery);
            },
            historyMedia() {
                return this.images(this.publicEventMediaCollections.history);
            },
        }
    }
</script>

<style scoped>
    .media-container > div {
        padding: 16px;
    }
</style>