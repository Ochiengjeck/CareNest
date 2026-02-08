import { Editor } from '@tiptap/core'
import StarterKit from '@tiptap/starter-kit'
import Placeholder from '@tiptap/extension-placeholder'
import Link from '@tiptap/extension-link'
import Underline from '@tiptap/extension-underline'

document.addEventListener('alpine:init', () => {
    Alpine.data('tiptapEditor', (initialContent = '', placeholder = 'Start writing...') => ({
        editor: null,

        init() {
            this.editor = new Editor({
                element: this.$refs.element,
                extensions: [
                    StarterKit.configure({
                        heading: { levels: [3, 4] },
                    }),
                    Placeholder.configure({ placeholder }),
                    Link.configure({ openOnClick: false }),
                    Underline,
                ],
                content: initialContent,
                editorProps: {
                    attributes: {
                        class: 'prose prose-sm dark:prose-invert max-w-none p-4 min-h-[150px] focus:outline-none',
                    },
                },
                onUpdate: ({ editor }) => {
                    this.$dispatch('tiptap-update', { content: editor.getHTML() })
                },
            })
        },

        isActive(name, attrs = {}) {
            return this.editor?.isActive(name, attrs) ?? false
        },

        toggleBold() { this.editor?.chain().focus().toggleBold().run() },
        toggleItalic() { this.editor?.chain().focus().toggleItalic().run() },
        toggleUnderline() { this.editor?.chain().focus().toggleUnderline().run() },
        toggleHeading(level) { this.editor?.chain().focus().toggleHeading({ level }).run() },
        toggleBulletList() { this.editor?.chain().focus().toggleBulletList().run() },
        toggleOrderedList() { this.editor?.chain().focus().toggleOrderedList().run() },

        setLink() {
            const url = prompt('Enter URL:')
            if (url) {
                this.editor?.chain().focus().setLink({ href: url }).run()
            }
        },

        unsetLink() {
            this.editor?.chain().focus().unsetLink().run()
        },

        destroy() {
            this.editor?.destroy()
        },
    }))
})
