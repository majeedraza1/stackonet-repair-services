<template>
		<div class="van-wrapper__box">
			<div class="van-wrapper__image-box">
				<slot name="icon"></slot>
			</div>
			<div class="van-wrapper__text-box">
				<div v-if="showOriginal">
					<slot></slot>
					<button class="van-wrapper__text-button" @click="handleViewMore">View More</button>
				</div>
				<div v-if="showHidden">
					<slot name="hidden-text"></slot>
					<button class="van-wrapper__text-button" @click="handleOriginal">View Original</button>
				</div>
			</div>
		</div>
</template>

<script>
    export default {
        name: "BuyVanBox",
        computed: {
            assets_url() {
                return Stackonet.assets
            },
        },
        data() {
            return {
                showOriginal: true,
                showHidden: false
            }
        },
        methods: {
            handleViewMore() {
                this.showOriginal = false;
                this.showHidden = true;
            },
            handleOriginal() {
                this.showOriginal = true;
                this.showHidden = false;
            }
        }
    }
</script>


<style lang="scss">
.hidden-text{

}
</style>
