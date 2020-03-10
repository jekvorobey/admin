<template>
    <div>
        <h3 class="mt-3">Изображения</h3>
        <div class="row">
            <div class="col">
                <div class="media-container d-flex flex-wrap align-items-stretch justify-content-start">
                    <div class="shadow mt-3 mr-3">
                        <img :src="mainImage.url" class="big-image">
                        Основная фотография
                        <fa-icon icon="trash-alt" class="float-right media-btn" @click="onDeleteImage(1, mainImage.id)"></fa-icon>
                        <fa-icon icon="pencil-alt" class="float-right media-btn" @click="startUploadImage(1, mainImage.id)"></fa-icon>
                    </div>
                    <div class="shadow mt-3 mr-3">
                        <img :src="catalogImage.url" class="big-image">
                        Фотография для каталога
                        <fa-icon icon="trash-alt" class="float-right media-btn" @click="onDeleteImage(2, mainImage.id)"></fa-icon>
                        <fa-icon icon="pencil-alt" class="float-right media-btn" @click="startUploadImage(2, mainImage.id)"></fa-icon>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="media-container d-flex flex-wrap align-items-stretch justify-content-start">
                    <div v-for="image in galleryImages" class="shadow mt-3 mr-3">
                        <img :src="image.url" class="small-image">
                        <fa-icon icon="trash-alt" class="float-right media-btn" @click="onDeleteImage(3, image.id)"></fa-icon>
                        <fa-icon icon="pencil-alt" class="float-right media-btn" @click="startUploadImage(3, image.id)"></fa-icon>
                    </div>
                    <div class="align-self-center">
                        <button class="btn btn-light" @click="startUploadImage(3)">Добавить</button>
                    </div>
                </div>
            </div>
        </div>
        <hr>
        <h3>Особенности</h3>
        <div class="row">
            <div class="col d-flex flex-row justify-content-start align-items-start">
                <template v-for="tip in product.tips">
                    <shadow-card :buttons="{onEdit:'pencil-alt', onDeleteImage:'trash-alt'}"
                                 @onEdit="editTip(tip)"
                                 @onDelete="deleteTip(product.id, tip.id)"
                                class="tip">
                        <img :src="imageUrl(tip.file_id)" class="big-image">
                        <div class="tip-description">{{tip.description}}</div>
                    </shadow-card>
                </template>
                <div class="align-self-center">
                    <button class="btn btn-light" @click="createNewTip">Добавить</button>
                </div>
            </div>
        </div>
        <hr>
        <h3>Описание</h3>
        <div class="row">
            <div class="col">
                <div class="d-flex flex-column justify-content-start align-items-start">
                    <shadow-card title="Текст" :buttons="{onEdit:'pencil-alt'}" @onEdit="openModal('DescriptionEdit')">
                        <div v-html="product.description"></div>
                    </shadow-card>
                </div>
            </div>
            <div class="col">
                <div class="d-flex flex-row justify-content-start align-items-start">
                    <shadow-card title="Видео" :buttons="{onEdit:'pencil-alt'}" @onEdit="openModal('DescriptionVideoEdit')">
                        <div v-if="product.description_video" class="embed-responsive embed-responsive-16by9 ">
                            <iframe
                                    width="424"
                                    height="238"
                                    class="embed-responsive-item"
                                    :src="videoUrl(product.description_video)"
                                    allowfullscreen></iframe>
                        </div>
                        <img v-else src="//placehold.it/424x238?text=No+video" class="embed-responsive embed-responsive-16by9 ">
                    </shadow-card>
                    <shadow-card
                            title="Изображение"
                            :buttons="{onEdit:'pencil-alt', onDelete:'trash-alt'}"
                            @onEdit="startUploadImage(4, descriptionImage.id)"
                            @onDelete="onDeleteImage(4, descriptionImage.id)">
                        <img :src="descriptionImage.url" class="big-image">
                    </shadow-card>
                </div>
            </div>
        </div>
        <hr>
        <h3>How to</h3>
        <div class="row">
            <div class="col">
                <div class="d-flex flex-column justify-content-start align-items-md-stretch">
                    <shadow-card title="Текст" :buttons="{onEdit:'pencil-alt'}" @onEdit="openModal('HowToEdit')">
                        <div v-html="product.how_to"></div>
                    </shadow-card>
                    <shadow-card title="Инструкция">
                        <template v-if="product.instruction_file_id">
                            <a class="btn btn-dark" :href="instructionUrl">Скачать</a>
                            <button @click="onDeleteInstruction" class="btn btn-danger">Удалить</button>
                        </template>
                        <button @click="openModal('InstructionUpload')" class="btn btn-success">Загрузить новый файл</button>
                    </shadow-card>
                </div>
            </div>
            <div class="col">
                <div class="d-flex flex-row justify-content-start align-items-start">
                    <shadow-card title="Видео" :buttons="{onEdit:'pencil-alt'}" @onEdit="openModal('HowToVideoEdit')">
                        <div v-if="product.how_to_video" class="embed-responsive embed-responsive-16by9 ">
                            <iframe
                                    width="424"
                                    height="238"
                                    class="embed-responsive-item"
                                    :src="videoUrl(product.how_to_video)"
                                    allowfullscreen></iframe>
                        </div>
                        <img v-else src="//placehold.it/424x238?text=No+video" class="embed-responsive embed-responsive-16by9 ">
                    </shadow-card>
                    <shadow-card
                            title="Изображение"
                            :buttons="{onEdit:'pencil-alt', onDelete:'trash-alt'}"
                            @onEdit="startUploadImage(5, howToImage.id)"
                            @onDelete="onDeleteImage(5, howToImage.id)">
                        <img :src="howToImage.url" class="big-image">
                    </shadow-card>
                </div>
            </div>
        </div>

        <file-upload-modal
                @accept="onAcceptImage"
                modal-name="ImageUpload"/>
        <file-upload-modal
                @accept="onAcceptInstruction"
                modal-name="InstructionUpload"/>
        <description-edit-modal
                :source="product"
                text_field="description"
                title="Редактирование описания товара"
                @onSave="$emit('onSave')"
                modal-name="DescriptionEdit"/>
        <description-edit-modal
                :source="product"
                text_field="how_to"
                title="Редактирование HOW TO товара"
                @onSave="$emit('onSave')"
                modal-name="HowToEdit"/>
        <video-edit-modal
                :source="product"
                video_field="description_video"
                @onSave="$emit('onSave')"
                modal-name="DescriptionVideoEdit"/>
        <video-edit-modal
                :source="product"
                video_field="how_to_video"
                @onSave="$emit('onSave')"
                modal-name="HowToVideoEdit"/>

        <transition name="modal">
            <modal :close="closeModal" v-if="isModalOpen('TipForm')">
                <div slot="header">
                    Добавление особенности
                </div>
                <div slot="body">
                    <tip-form
                            :product="product"
                            :tip="currentTip"
                            @onSave="onTipSaved"/>
                </div>
            </modal>
        </transition>
    </div>
</template>

<script>
    import modalMixin from '../../../../mixins/modal';

    import Modal from '../../../../components/controls/modal/modal.vue';
    import ShadowCard from '../../../../components/shadow-card.vue';
    import FileUploadModal from './file-upload-modal.vue';
    import DescriptionEditModal from './product-description-modal.vue';
    import VideoEditModal from './product-video-modal.vue';
    import TipForm from './tip-form.vue';

    import Services from "../../../../../scripts/services/services";
    import {mapGetters} from "vuex";
    import Media from "../../../../../scripts/media";

    export default {
        components: {
            Modal,
            ShadowCard,
            FileUploadModal,
            DescriptionEditModal,
            VideoEditModal,
            TipForm
        },
        mixins: [modalMixin],
        props: {
            images: {},
            product: {},
        },
        data() {
            return {
                currentType: 0,
                replaceFileId: undefined,
                currentTip: {}
            };
        },
        methods: {
            startUploadImage(type, replaceFileId) {
                this.currentType = type;
                this.replaceFileId = replaceFileId;
                this.openModal('ImageUpload');
            },
            onAcceptImage(file) {
                if (this.replaceFileId) {
                    this.onDeleteImage(this.currentType, this.replaceFileId);
                }
                Services.net().post(this.getRoute('products.saveImage', {id: this.product.id}), {}, {
                    id: file.id,
                    type: this.currentType,
                })
                    .then(() => {
                        this.$emit('onSave');
                        this.closeModal();
                    });
            },
            onDeleteImage(type, fileId) {
                if (!fileId) {
                    return;
                }
                Services.net().post(this.getRoute('products.deleteImage', {id: this.product.id}), {}, {
                    id: fileId,
                    type: type,
                })
                    .then(() => {
                        this.$emit('onSave');
                    });
            },
            onAcceptInstruction(file) {
                let route = this.getRoute('products.saveProduct', {id: this.product.id});
                Services.net().post(route, {}, {instruction_file_id: file.id})
                    .then(()=> {
                        this.$emit('onSave');
                    });
            },
            onDeleteInstruction() {
                let route = this.getRoute('products.saveProduct', {id: this.product.id});
                Services.net().post(route, {}, {instruction_file_id: null})
                    .then(()=> {
                        this.$emit('onSave');
                    });
            },
            imageUrl(id) {
                return Media.compressed(id, 290, 290);
            },
            videoUrl(code) {
                return Media.video(code);
            },
            createNewTip() {
                this.currentTip = {
                    description: "",
                    fileId: 0
                };
                this.openModal('TipForm');
            },
            editTip(tip) {
                this.currentTip = tip;
                this.openModal('TipForm');
            },
            deleteTip(productId, tipId) {
                Services.net().post(this.getRoute('product.deleteTip', {id: productId, tipId: tipId}))
                    .then(result => {
                        this.$emit('onSave', result);
                    });
            },
            onTipSaved() {
                this.closeModal('TipForm');
                this.$emit('onSave');
            }
        },
        computed: {
            ...mapGetters(['getRoute']),
            mainImage() {
                let mainImages = this.images.filter(image => image.type === 1);
                return mainImages.length > 0 ? mainImages[0] : {
                    id: 0,
                    url: Media.empty(150, 150),
                };
            },
            catalogImage() {
                let catalogImages = this.images.filter(image => image.type === 2);
                return catalogImages.length > 0 ? catalogImages[0] : {
                    id: 0,
                    url: Media.empty(150, 150),
                };
            },
            descriptionImage() {
                let descriptionImages = this.images.filter(image => image.type === 4);
                return descriptionImages.length > 0 ? descriptionImages[0] : {
                    id: 0,
                    url: Media.empty(150, 150),
                };
            },
            howToImage() {
                let howToImages = this.images.filter(image => image.type === 5);
                return howToImages.length > 0 ? howToImages[0] : {
                    id: 0,
                    url: Media.empty(150, 150),
                };
            },
            galleryImages() {
                return this.images.filter(image => image.type === 3);
            },
            instructionUrl() {
                return Media.file(this.product.instruction_file_id);
            }
        }
    }
</script>

<style scoped>
    .media-container > div {
        padding: 16px;
    }
    .big-image {
        height: calc( 298px - 16px * 2 );
        display: block;
        max-width: 290px;
    }
    .small-image {
        height: calc( 130px - 16px * 2 );
        display: block;
    }
    .embed-responsive {
        height: calc( 298px - 16px * 2 );
        min-width: 300px;
    }
    .media-btn {
        margin-top: 6px;
        margin-right: 6px;
        color: gray;
        transition: 0.3s all;
        cursor: pointer;
    }
    .media-btn:hover {
        color: black;
    }
    .card-head {
        height: 48px;
        padding: 8px 16px;
    }
    .corner-edit-btn {
        position: relative;
        float: right;
        top: 5px;
        color: gray;
        transition: 0.3s all;
        cursor: pointer;
    }
    .corner-edit-btn:hover {
        color: black;
    }
    .big-add-btn {
        transform: scale(3);
        color: gray;
        transition: 0.3s all;
        cursor: pointer;
    }
    .big-add-btn:hover {
        color: black;
    }
    .tip-description {
        width: 260px;
    }
</style>