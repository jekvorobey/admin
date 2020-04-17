<template>
    <div class="shadow mt-3 mr-3">
        <div
                v-if="mediaObject.type === publicEventMediaTypes.youtube"
                :class="{'embed-responsive-small': small}"
                class="embed-responsive embed-responsive-16by9"
        >
            <iframe
                    width="424"
                    height="238"
                    class="embed-responsive-item"
                    :src="youtubeUrl"
                    allowfullscreen>
            </iframe>
        </div>
        <a v-else-if="mediaObject.type === publicEventMediaTypes.image" :href="fileUrl" target="_blank">
            <img :src="imageUrl"
                 :class="{'big-image': !small, 'small-image': small}"
                 alt="Image">
        </a>
        <div v-else>
            <a :href="fileUrl">Скачать</a>
        </div>
        <slot />
        <fa-icon icon="trash-alt" class="float-right media-btn" @click="$emit('onDelete', mediaObject.id)" />
        <fa-icon icon="pencil-alt" class="float-right media-btn" @click="$emit('onEdit', mediaObject.id)" />
    </div>
</template>

<script>
    import Media from '../../../../../scripts/media';

    export default {
        props: {
            mediaObject: {},
            small: Boolean
        },
        computed: {
            imageUrl() {
                let size = this.small ? 150 : 300;
                let url;
                if (this.mediaObject.value) {
                    url = Media.compressed(this.mediaObject.value, size, size);
                } else {
                    url = Media.empty(size, size);
                }
                return url;
            },
            youtubeUrl() {
                return Media.video(this.mediaObject.value);
            },
            fileUrl() {
                return Media.file(this.mediaObject.value);
            }
        }
    }
</script>

<style scoped>
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
    .embed-responsive-small {
        height: calc( 130px - 16px * 2 ) !important;
        min-width: 150px !important;
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
</style>