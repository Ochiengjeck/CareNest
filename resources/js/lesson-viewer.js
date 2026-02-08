document.addEventListener('alpine:init', () => {
    Alpine.data('lessonViewer', (sectionCount) => ({
        openSection: -2, // -2 = all expanded by default
        isFullscreen: false,
        lightboxImage: null,
        lightboxCaption: null,

        toggleSection(idx) {
            this.openSection = this.openSection === idx ? -1 : idx;
        },

        scrollToSection(idx) {
            this.$nextTick(() => {
                const container = this.isFullscreen ? this.$refs.fullscreenContent : this.$refs.normalContent;
                if (!container) return;
                const el = container.querySelector(`[data-section-idx="${idx}"]`);
                if (el) {
                    el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        },

        openLightbox(src, caption) {
            this.lightboxImage = src;
            this.lightboxCaption = caption || null;
        },

        expandAll() {
            this.openSection = -2; // special: means all open
        },

        collapseAll() {
            this.openSection = -1;
        },

        isSectionOpen(idx) {
            return this.openSection === idx || this.openSection === -2;
        }
    }));
});
