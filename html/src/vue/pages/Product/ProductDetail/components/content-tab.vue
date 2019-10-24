<template>
    <div>
        <div class="row">
            <div class="col-lg-4 col-md col-sm-12">
                <div class="media-container d-flex flex-wrap align-items-stretch justify-content-start">
                    <div class="shadow mt-3 mr-3">
                        <img :src="mainImage.url" class="big-image">
                        Основная фотография
                        <fa-icon icon="trash-alt" class="float-right media-btn" @click="onDelete(mainImage.type, mainImage.id)"></fa-icon>
                        <fa-icon icon="pencil-alt" class="float-right media-btn" @click="startUploadFile(mainImage.type, mainImage.id)"></fa-icon>
                    </div>
                </div>
            </div>
            <div class="col-lg-8 col-md col-sm-12">
                <div class="media-container d-flex flex-wrap align-items-stretch justify-content-start">
                    <div v-for="image in galleryImages" class="shadow mt-3 mr-3">
                        <img :src="image.url" class="small-image">
                        <fa-icon icon="trash-alt" class="float-right media-btn"></fa-icon>
                        <fa-icon icon="pencil-alt" class="float-right media-btn"></fa-icon>
                    </div>
                    <div class="align-self-center">
                        <button class="btn btn-light">Добавить</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="d-flex flex-column justify-content-start align-items-start">
            <div class="shadow mt-3 mr-3" style="max-width: 500px; min-width: 300px">
                <div class="card-head">
                    Описание товара
                    <fa-icon icon="pencil-alt" class="corner-edit-btn"></fa-icon>
                </div>
                <div class="px-5 pb-5">
                    <p>{{ product.description }}</p>
                </div>
            </div>
            <div class="shadow mt-3 mr-3" style="max-width: 500px">
                <div class="card-head">
                    How to use
                    <fa-icon icon="pencil-alt" class="corner-edit-btn"></fa-icon>
                </div>
                <div class="px-5 pb-5">
                    <div class="embed-responsive embed-responsive-16by9 ">
                        <iframe width="424" height="238" class="embed-responsive-item" src="https://www.youtube.com/embed/yKRxxMuHthg" allowfullscreen></iframe>
                    </div>
                </div>
            </div>
        </div>
        <file-upload-modal @accept="onAccept" modal-name="FileUpload"></file-upload-modal>
    </div>
</template>

<script>
    import modalMixin from '../../../../mixins/modal';
    import FileUploadModal from './file-upload-modal.vue';
    import Services from "../../../../../scripts/services/services";
    import {mapGetters} from "vuex";
    export default {
        components: {FileUploadModal},
        mixins: [modalMixin],
        props: {
            images: {},
            product: {},
        },
        data() {
            return {
                currentType: 0,
                replaceFileId: undefined,
            };
        },
        methods: {
            startUploadFile(type, replaceFileId) {
                this.currentType = type;
                this.replaceFileId = replaceFileId;
                this.openModal('FileUpload');
            },
            onAccept(file) {
                if (this.replaceFileId) {
                    this.onDelete(this.currentType, this.replaceFileId);
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
            onDelete(type, fileId) {
                Services.net().post(this.getRoute('products.deleteImage', {id: this.product.id}), {}, {
                    id: fileId,
                    type: type,
                })
                    .then(() => {
                        this.$emit('onSave');
                    });
            }
        },
        computed: {
            ...mapGetters(['getRoute']),
            mainImage() {
                let mainImages = this.images.filter(image => image.type === 1);
                return mainImages.length > 0 ? mainImages[0] : {};
            },
            galleryImages() {
                return this.images.filter(image => image.type === 3);
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
    }
    .small-image {
        height: calc( 130px - 16px * 2 );
        display: block;
    }
    .embed-responsive {
        height: calc( 300px - 16px * 2 );
        width: 400px;
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
</style>