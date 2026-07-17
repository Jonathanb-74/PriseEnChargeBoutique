document.addEventListener('alpine:init', () => {
    Alpine.data('signaturePad', (property) => ({
        drawing: false,
        empty: true,
        fullscreen: false,

        init() {
            const canvas = this.$refs.canvas;
            const ctx = canvas.getContext('2d');

            this.resize = () => {
                const ratio = window.devicePixelRatio || 1;
                const data = this.empty ? null : canvas.toDataURL('image/png');
                const previousWidth = canvas.width;
                const previousHeight = canvas.height;
                canvas.width = canvas.offsetWidth * ratio;
                canvas.height = canvas.offsetHeight * ratio;
                ctx.scale(ratio, ratio);
                ctx.lineWidth = 2;
                ctx.lineCap = 'round';
                ctx.strokeStyle = '#111827';

                if (data) {
                    const img = new Image();
                    img.onload = () => {
                        // Fit the previous drawing inside the new canvas size without
                        // stretching it out of proportion (e.g. when entering fullscreen).
                        const boxW = canvas.offsetWidth;
                        const boxH = canvas.offsetHeight;
                        const oldRatio = previousWidth && previousHeight ? previousWidth / previousHeight : boxW / boxH;
                        let w = boxW;
                        let h = w / oldRatio;
                        if (h > boxH) {
                            h = boxH;
                            w = h * oldRatio;
                        }
                        ctx.drawImage(img, (boxW - w) / 2, (boxH - h) / 2, w, h);
                    };
                    img.src = data;
                }
            };

            this.resize();
            window.addEventListener('resize', this.resize);

            const pos = (e) => {
                const rect = canvas.getBoundingClientRect();
                const point = e.touches ? e.touches[0] : e;
                return { x: point.clientX - rect.left, y: point.clientY - rect.top };
            };

            const start = (e) => {
                e.preventDefault();
                this.drawing = true;
                const { x, y } = pos(e);
                ctx.beginPath();
                ctx.moveTo(x, y);
            };

            const move = (e) => {
                if (!this.drawing) return;
                e.preventDefault();
                const { x, y } = pos(e);
                ctx.lineTo(x, y);
                ctx.stroke();
                this.empty = false;
            };

            const end = () => {
                if (!this.drawing) return;
                this.drawing = false;
                this.$wire.set(property, canvas.toDataURL('image/png'), false);
            };

            canvas.addEventListener('mousedown', start);
            canvas.addEventListener('mousemove', move);
            window.addEventListener('mouseup', end);
            canvas.addEventListener('touchstart', start, { passive: false });
            canvas.addEventListener('touchmove', move, { passive: false });
            canvas.addEventListener('touchend', end);
        },

        toggleFullscreen() {
            this.fullscreen = !this.fullscreen;
            document.body.style.overflow = this.fullscreen ? 'hidden' : '';
            this.$nextTick(() => this.resize());
        },

        clear() {
            const canvas = this.$refs.canvas;
            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            this.empty = true;
            this.$wire.set(property, null, false);
        },
    }));
});
