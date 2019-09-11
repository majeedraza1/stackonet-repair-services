<template>
	<div class="shapla-featured-image">
		<div class="shapla-featured-image__view">
			<div class="shapla-featured-image__thumbnail" v-if="has_image">
				<img :src="image.attachment_url" alt=""/>
			</div>
			<div class="shapla-featured-image__placeholder" v-if="!has_image">
				{{placeholderText}}
			</div>
			<div class="shapla-featured-image__actions">
				<button class="button" @click="clearImages" v-if="has_image">{{removeButtonText}}</button>
				<button class="button" @click="active = true" v-if="!has_image">{{buttonText}}</button>
			</div>
		</div>
		<media-modal
			:active="active"
			:options="dropzoneOptions"
			:images="images"
			@close="active = false"
			@selected="chooseImage"
		></media-modal>
	</div>
</template>

<script>
    import MediaModal from "../frontend/components/MediaModal";

    export default {
        name: "FeaturedImage",
        components: {MediaModal},
        props: {
            placeholderText: {type: String, default: 'No File Selected'},
            buttonText: {type: String, default: 'Add Image'},
            removeButtonText: {type: String, default: 'Remove'},
            modalTitle: {type: String, default: 'Select Image'},
            modalButtonText: {type: String, default: 'Set Image'},
            image: {type: [Object, Array], default: () => []},
            images: {type: Array, default: () => []},
        },
        data() {
            return {
                active: false,
            }
        },
        computed: {
            has_image() {
                return !!(this.image && this.image.attachment_url);
            },
            dropzoneOptions() {
                return {
                    url: window.PhoneRepairs.rest_root + '/logo',
                    maxFilesize: 5,
                    headers: {
                        "X-WP-Nonce": window.PhoneRepairs.rest_nonce,
                    }
                }
            },
        },
        methods: {
            chooseImage(image) {
                this.$emit('input', image);
                this.active = false;
            },
            openMediaModal() {
                this.$emit('input', {
                    id: 0,
                    src: '',
                    height: 0,
                    width: 0,
                });
            },
            clearImages() {
                if (confirm('Are you sure?')) {
                    this.$emit('input', {});
                }
            }
        }
    }
</script>

<style lang="scss" scoped>
	.shapla-featured-image {
		position: relative;

		&__placeholder {
			border: 1px dashed #b4b9be;
			box-sizing: border-box;
			cursor: default;
			line-height: 20px;
			margin-bottom: 10px;
			padding: 9px 0;
			position: relative;
			text-align: center;
			width: 100%;
		}

		&__thumbnail {
			max-width: 150px;

			img {
				max-width: 100%;
				height: auto;
			}
		}
	}
</style>
