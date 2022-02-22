<template>
    <div>
        <h3 class="mt-3">Изображения</h3>
        <div class="row">
            <div class="col">
                <draggable
                    v-model="galleryImages"
                    animation="200"
                    :options="{
                        disabled: !canUpdate(blocks.products)
                    }"
                    @start="drag = true"
                    @end="drag = false"
                    @change="onGallerySort"
                >
                    <transition-group
                        :name="!drag ? 'flip-list' : null"
                        type="transition"
                        class="media-container d-flex flex-wrap align-items-stretch justify-content-start"
                    >
                        <div v-for="image in galleryImages" :key="image.id" class="shadow mt-3 mr-3">
                            <img :src="image.url" class="small-image">

                            <fa-icon
                                v-if="canUpdate(blocks.products)"
                                icon="trash-alt"
                                class="float-right media-btn"
                                @click="onDeleteImage(productImageType.gallery, image.id)"
                            ></fa-icon>
                        </div>
                    </transition-group>
                </draggable>
            </div>
        </div>
        <div v-if="canUpdate(blocks.products)" class="row">
            <div class="col p-3">
                <div class="align-self-center">
                    <button class="btn btn-light" @click="startUploadImage(productImageType.gallery)">Добавить</button>
                </div>
            </div>
        </div>
        <hr>
        <h3>Особенности</h3>
        <div class="row">
            <div class="col d-flex flex-row justify-content-start align-items-start">
                <template v-for="tip in product.tips">
                    <shadow-card :buttons="canUpdate(blocks.products) ? {onEdit:'pencil-alt', onDeleteTip:'trash-alt'} : {}"
                                 @onEdit="editTip(tip)"
                                 @onDeleteTip="deleteTip(product.id, tip.id)"
                                class="tip">
                        <img :src="imageUrl(tip.file_id)" class="big-image">
                        <div class="tip-description">{{tip.description}}</div>
                    </shadow-card>
                </template>
                <div class="align-self-center" v-if="canUpdate(blocks.products)">
                    <button class="btn btn-light" @click="createNewTip">Добавить</button>
                </div>
            </div>
        </div>
        <hr>
        <h3>Описание</h3>
        <div class="row">
            <div class="col">
                <div class="d-flex flex-column justify-content-start align-items-start">
                    <shadow-card title="Текст" :buttons="canUpdate(blocks.products) ? {onEdit:'pencil-alt', onDelete:'trash-alt'} : {}"
                                 @onEdit="openModal('DescriptionEdit')"
                                 @onDelete="deleteDescriptionText">
                        {{ product.description }}
                    </shadow-card>
                </div>
            </div>
            <div class="col">
                <div class="d-flex flex-row justify-content-start align-items-start">
                    <shadow-card title="Видео" :buttons="canUpdate(blocks.products) ? {onEdit:'pencil-alt', onDelete:'trash-alt'} : {}"
                                 @onEdit="openModal('DescriptionVideoEdit')"
                                 @onDelete="deleteDescriptionVideo">
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
                            :buttons="canUpdate(blocks.products) ? {onEdit:'pencil-alt', onDelete:'trash-alt'} : {}"
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
                    <shadow-card title="Текст" :buttons="canUpdate(blocks.products) ? {onEdit:'pencil-alt', onDelete:'trash-alt'} : {}"
                                 @onEdit="openModal('HowToEdit')" @onDelete="deleteHowToText">
                        <ol v-if="product.how_to">
                            <li v-for="item in howToList">
                                {{ item }}
                            </li>
                        </ol>
                        <em v-else>
                            (Способ применения не указан)
                        </em>
                    </shadow-card>
                    <shadow-card title="Инструкция">
                        <template v-if="product.instruction_file_id">
                            <a class="btn btn-dark" :href="instructionUrl">Скачать</a>
                            <button @click="onDeleteInstruction" v-if="canUpdate(blocks.products)" class="btn btn-danger">Удалить</button>
                        </template>
                        <button @click="openModal('InstructionUpload')" v-if="canUpdate(blocks.products)" class="btn btn-success">Загрузить новый файл</button>
                    </shadow-card>
                </div>
            </div>
            <div class="col">
                <div class="d-flex flex-row justify-content-start align-items-start">
                    <shadow-card
                            title="Видео"
                            :buttons="canUpdate(blocks.products) ? {onEdit:'pencil-alt', onDelete:'trash-alt'} : {}"
                            @onEdit="openModal('HowToVideoEdit')"
                            @onDelete="deleteHowToVideo">
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
                            :buttons="canUpdate(blocks.products) ? {onEdit:'pencil-alt', onDelete:'trash-alt'} : {}"
                            @onEdit="startUploadImage(5, howToImage.id)"
                            @onDelete="onDeleteImage(5, howToImage.id)">
                        <img :src="howToImage.url" class="big-image">
                    </shadow-card>
                </div>
            </div>
        </div>
        <hr>
        <h3>Мастер-классы</h3>
        <table class="table table-sm">
            <tbody>
                <tr>
                    <th v-if="canUpdate(blocks.products)">
                        Добавить
                        <button class="btn btn-success btn-sm" @click="openModal('ProductPublicEventModal')"><fa-icon icon="plus"/></button>
                    </th>
                    <td>
                        <div v-for="(event, index) in product.publicEvents">
                            {{ event.name }}
                            <span v-if="canUpdate(blocks.products)">
                                <button class="btn btn-outline-secondary btn-sm btn-icon" @click="detachPublicEvent(index)">
                                    <fa-icon icon="times"/>
                                </button>
                            </span>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <images-upload-modal
                @accept="onAcceptImage"
                modal-name="ImageUpload"
        />

        <file-upload-modal
            @accept="onAcceptImage"
            modal-name="ImageUploadSingle"/>

        <file-upload-modal
                @accept="onAcceptInstruction"
                modal-name="InstructionUpload"/>

        <description-edit-modal
                :source="currentProduct"
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
        <product-public-event-modal
            :product-id="product.id"
            :public-events="product.publicEvents"
            :all-public-events="allPublicEvents"
            @onSave="$emit('onSave')"
            modal-name="ProductPublicEventModal"/>

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
import draggable from 'vuedraggable';

import modalMixin from '../../../../mixins/modal';

import Modal from '../../../../components/controls/modal/modal.vue';
import ShadowCard from '../../../../components/shadow-card.vue';
import FileUploadModal from './file-upload-modal.vue';
import ImagesUploadModal from './images-upload-modal.vue';
import DescriptionEditModal from './product-description-modal.vue';
import VideoEditModal from './product-video-modal.vue';
import ProductPublicEventModal from './product-public-event-modal.vue';
import TipForm from './tip-form.vue';

import Services from '../../../../../scripts/services/services';
import Media from '../../../../../scripts/media';

export default {
    components: {
        Modal,
        ShadowCard,
        FileUploadModal,
        ImagesUploadModal,
        DescriptionEditModal,
        VideoEditModal,
        ProductPublicEventModal,
        TipForm,
        draggable
    },

    mixins: [modalMixin],

    props: {
        images: {},
        product: {},
        allPublicEvents: Array,
    },

    data() {
        return {
            currentType: 0,
            replaceFileId: undefined,
            currentTip: {},
            currentProduct: Object.assign({}, this.product),
            galleryImages: this.images.filter(image => image.type === this.$store.state.layout.productImageTypes.gallery),
            drag: false,
        };
    },

    watch: {
        images() {
            this.galleryImages = this.images.filter(image => image.type === this.productImageType.gallery);
        }
    },

    methods: {
        startUploadImage(type, replaceFileId) {
            this.currentType = type;
            this.replaceFileId = replaceFileId;

            if (this.currentType === this.productImageType.gallery) {
                this.openModal('ImageUpload');
            } else {
                this.openModal('ImageUploadSingle');
            }
        },

        async onGallerySort() {
            Services.showLoader();

            await Services.net().post(
                this.getRoute('products.sortImages', { id: this.product.id }),
                {},
                {
                    images_ids: this.galleryImages.map(image => image.productImageId),
                    type: this.productImageType.gallery
                }
            );

            Services.hideLoader();

            this.$emit('onSave');
        },

        async onAcceptImage(files) {
            Services.showLoader();

            let tFiles = files;

            if (!Array.isArray(tFiles)) {
                tFiles = [ tFiles ];
            }

            if (this.replaceFileId) {
                this.onDeleteImage(this.currentType, this.replaceFileId);
            }

            for (const file of tFiles) {
                try {
                    await Services.net().post(
                        this.getRoute('products.saveImage', { id: this.product.id }),
                        {},
                        {
                            id: file.id,
                            type: this.currentType
                        }
                    );
                } catch (error) {
                    console.error(error);
                }
            }

            Services.hideLoader();

            this.$emit('onSave');
            this.closeModal();
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
        },
        deleteHowToText() {
            let route = this.getRoute('products.saveProduct', {id: this.product.id});
            Services.net().post(route, {}, {how_to: null})
                .then(()=> {
                    this.$emit('onSave');
                });
        },
        deleteHowToVideo() {
            let route = this.getRoute('products.saveProduct', {id: this.product.id});
            Services.net().post(route, {}, {how_to_video: null})
                .then(()=> {
                    this.$emit('onSave');
                });
        },
        deleteDescriptionVideo() {
            let route = this.getRoute('products.saveProduct', {id: this.product.id});
            Services.net().post(route, {}, {description_video: null})
                .then(()=> {
                    this.$emit('onSave');
                });
        },
        deleteDescriptionText() {
            let route = this.getRoute('products.saveProduct', {id: this.product.id});
            Services.net().post(route, {}, {description: null})
                .then(()=> {
                    this.currentProduct.description = null;
                    this.$emit('onSave');
                });
        },
        detachPublicEvent(index) {
            this.product.publicEvents.splice(index, 1);
            let remainingPublicEvents = this.pluck(this.product.publicEvents, 'id');

            Services.net().put(this.getRoute('products.savePublicEvents', {id: this.product.id}), null,
                {'public_events': remainingPublicEvents.map(value => parseInt(value))})
                .then(result => {
                    this.$emit('onSave', result);
                })
        },
        pluck(objects, keyName) {
            var sol = [];
            for(var i in objects){
                if(objects[i].hasOwnProperty(keyName)){
                    sol.push(objects[i][keyName]);
                }
            }
            return sol;
        }
    },
    computed: {
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
        howToList() {
            return this.product.how_to.split('|');
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

    .flip-list-move {
        transition: transform 0.5s;
    }

    .no-move {
        transition: transform 0s;
    }

    .sortable-ghost {
        opacity: 0.5;
    }
</style>
