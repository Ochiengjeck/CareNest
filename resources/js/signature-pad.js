import SignaturePad from 'signature_pad';

// Expose globally so inline Alpine components can instantiate the pad directly
window.SignaturePad = SignaturePad;

document.addEventListener('alpine:init', () => {
    Alpine.data('signatureCreator', (initialPenColor = '#000000') => ({
        pad: null,
        isEmpty: true,
        saving: false,
        penColor: initialPenColor,
        _observer: null,

        init() {
            this.$watch('penColor', (color) => {
                if (this.pad) this.pad.penColor = color;
            });

            this.$nextTick(() => this._tryInit());
        },

        _tryInit() {
            const canvas = this.$refs.canvas;
            if (!canvas) return;

            if (canvas.offsetWidth > 0) {
                // Canvas already visible — set up immediately
                this._setupPad(canvas);
            } else {
                // Canvas is inside a closed/hidden dialog.
                // Watch for it to enter the viewport (when modal opens).
                this._observer = new IntersectionObserver((entries) => {
                    if (entries[0].isIntersecting) {
                        this._observer.disconnect();
                        this._observer = null;
                        // One rAF to let the dialog finish its open transition
                        requestAnimationFrame(() => {
                            const c = this.$refs.canvas;
                            if (c) this._setupPad(c);
                        });
                    }
                });
                this._observer.observe(canvas);
            }
        },

        _setupPad(canvas) {
            if (this.pad) return; // already initialized

            canvas.width  = canvas.offsetWidth;
            canvas.height = canvas.offsetHeight;

            this.pad = new SignaturePad(canvas, {
                backgroundColor: 'rgb(255, 255, 255)',
                penColor: this.penColor,
                minWidth: 1,
                maxWidth: 3,
            });

            this.pad.addEventListener('beginStroke', () => {
                this.isEmpty = false;
            });

            window.addEventListener('resize', () => this._resizeCanvas());
        },

        _resizeCanvas() {
            const canvas = this.$refs.canvas;
            if (!canvas || !this.pad || !canvas.offsetWidth) return;

            canvas.width  = canvas.offsetWidth;
            canvas.height = canvas.offsetHeight;
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
            if (!this.pad || this.pad.isEmpty() || this.saving) return;
            this.saving = true;
            const dataUrl = this.pad.toDataURL('image/png');
            this.$wire.call('saveDrawnSignature', dataUrl, this.penColor)
                .finally(() => { this.saving = false; });
        },

        destroy() {
            if (this._observer) {
                this._observer.disconnect();
                this._observer = null;
            }
            window.removeEventListener('resize', () => this._resizeCanvas());
            if (this.pad) this.pad.off();
        },
    }));
});
