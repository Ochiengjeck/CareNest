import SignaturePad from 'signature_pad';

document.addEventListener('alpine:init', () => {
    Alpine.data('signaturePad', (existingSignature) => ({
        pad: null,
        isEmpty: true,

        init() {
            const canvas = this.$refs.canvas;
            if (!canvas) return;

            this.pad = new SignaturePad(canvas, {
                backgroundColor: 'rgb(255, 255, 255)',
                penColor: 'rgb(0, 0, 0)',
            });

            this.pad.addEventListener('beginStroke', () => {
                this.isEmpty = false;
            });

            this.resizeCanvas();
            window.addEventListener('resize', () => this.resizeCanvas());

            if (existingSignature) {
                this.isEmpty = false;
            }
        },

        resizeCanvas() {
            const canvas = this.$refs.canvas;
            if (!canvas || !this.pad) return;

            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            const rect = canvas.getBoundingClientRect();

            canvas.width = rect.width * ratio;
            canvas.height = rect.height * ratio;
            canvas.getContext('2d').scale(ratio, ratio);

            this.pad.clear();
            this.isEmpty = true;
        },

        clear() {
            if (this.pad) {
                this.pad.clear();
                this.isEmpty = true;
            }
        },

        save() {
            if (!this.pad || this.pad.isEmpty()) return;

            const dataUrl = this.pad.toDataURL('image/png');
            this.$wire.call('receiveSignature', dataUrl);
        },

        destroy() {
            window.removeEventListener('resize', () => this.resizeCanvas());
            if (this.pad) {
                this.pad.off();
            }
        }
    }));
});
