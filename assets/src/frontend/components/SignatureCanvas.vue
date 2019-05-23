<template>
	<div>
		<columns mobile multiline>
			<column :mobile="12" :tablet="7">
				<canvas id="stackonet-signature-canvas" class="stackonet-signature-canvas" width="270"
						height="150"></canvas>
			</column>
			<column :mobile="12" :tablet="5">
				<p>
					<mdl-button type="raised" class="button--clear">Clear Signature</mdl-button>
				</p>
				<p>
					<mdl-button type="raised" color="primary" class="button--create-signature">
						Create Signature
					</mdl-button>
				</p>
			</column>
		</columns>
	</div>
</template>

<script>
	import columns from '../../shapla/columns/columns'
	import column from '../../shapla/columns/column'
	import mdlButton from '../../material-design-lite/button/mdlButton'

	export default {
		name: "SignatureCanvas",
		components: {columns, column, mdlButton},
		mounted() {
			let self = this;
			let canvas = self.$el.querySelector('#stackonet-signature-canvas');
			let clearButton = self.$el.querySelector('.button--clear');
			let createSign = self.$el.querySelector('.button--create-signature');

			// Get a regular interval for drawing to the screen
			window.requestAnimFrame = (function (callback) {
				return window.requestAnimationFrame ||
					window.webkitRequestAnimationFrame ||
					window.mozRequestAnimationFrame ||
					window.oRequestAnimationFrame ||
					window.msRequestAnimaitonFrame ||
					function (callback) {
						window.setTimeout(callback, 1000 / 60);
					};
			})();

			// Set up the canvas
			let ctx = canvas.getContext("2d");
			ctx.strokeStyle = "#222222";
			ctx.lineWith = 2;

			clearButton.addEventListener("click", function () {
				if (confirm('Are you sure?')) {
					clearCanvas();
					self.$emit('clear');
				}
			}, false);

			createSign.addEventListener("click", function () {
				let dataUrl = canvas.toDataURL();
				self.$emit('save', dataUrl);
			}, false);

			// Set up mouse events for drawing
			let drawing = false;
			let mousePos = {x: 0, y: 0};
			let lastPos = mousePos;

			canvas.addEventListener("mousedown", function (e) {
				drawing = true;
				lastPos = getMousePos(canvas, e);
			}, false);

			canvas.addEventListener("mouseup", function (e) {
				drawing = false;
			}, false);

			canvas.addEventListener("mousemove", function (e) {
				mousePos = getMousePos(canvas, e);
			}, false);

			// Set up touch events for mobile, etc
			canvas.addEventListener("touchstart", function (e) {
				mousePos = getTouchPos(canvas, e);
				let touch = e.touches[0];
				let mouseEvent = new MouseEvent("mousedown", {
					clientX: touch.clientX,
					clientY: touch.clientY
				});
				canvas.dispatchEvent(mouseEvent);
			}, false);

			canvas.addEventListener("touchend", function (e) {
				let mouseEvent = new MouseEvent("mouseup", {});
				canvas.dispatchEvent(mouseEvent);
			}, false);

			canvas.addEventListener("touchmove", function (e) {
				let touch = e.touches[0];
				let mouseEvent = new MouseEvent("mousemove", {
					clientX: touch.clientX,
					clientY: touch.clientY
				});
				canvas.dispatchEvent(mouseEvent);
			}, false);

			// Prevent scrolling when touching the canvas
			document.body.addEventListener("touchstart", function (e) {
				if (e.target === canvas) {
					e.preventDefault();
				}
			}, {capture: false, passive: false});
			document.body.addEventListener("touchend", function (e) {
				if (e.target === canvas) {
					e.preventDefault();
				}
			}, {capture: false, passive: false});
			document.body.addEventListener("touchmove", function (e) {
				if (e.target === canvas) {
					e.preventDefault();
				}
			}, {capture: false, passive: false});

			// Get the position of the mouse relative to the canvas
			function getMousePos(canvasDom, mouseEvent) {
				let rect = canvasDom.getBoundingClientRect();
				return {
					x: mouseEvent.clientX - rect.left,
					y: mouseEvent.clientY - rect.top
				};
			}

			// Get the position of a touch relative to the canvas
			function getTouchPos(canvasDom, touchEvent) {
				let rect = canvasDom.getBoundingClientRect();
				return {
					x: touchEvent.touches[0].clientX - rect.left,
					y: touchEvent.touches[0].clientY - rect.top
				};
			}

			// Draw to the canvas
			function renderCanvas() {
				if (drawing) {
					ctx.moveTo(lastPos.x, lastPos.y);
					ctx.lineTo(mousePos.x, mousePos.y);
					ctx.stroke();
					lastPos = mousePos;
				}
			}

			function clearCanvas() {
				canvas.width = canvas.width;
			}

			// Allow for animation
			(function drawLoop() {
				requestAnimFrame(drawLoop);
				renderCanvas();
			})();
		}
	}
</script>

<style lang="scss">
	.stackonet-signature-canvas {
		border: 1px solid rgba(#000, 0.2);
	}
</style>
