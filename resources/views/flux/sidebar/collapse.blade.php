@blaze(fold: true, unsafe: ['tooltip:position', 'tooltip:kbd', 'tooltip'])

@php $tooltipPosition = $tooltipPosition ??= $attributes->pluck('tooltip:position'); @endphp
@php $tooltipKbd = $tooltipKbd ??= $attributes->pluck('tooltip:kbd'); @endphp
@php $tooltip = $tooltip ??= $attributes->pluck('tooltip'); @endphp

@props([
    'tooltipPosition' => 'right',
    'tooltipKbd' => null,
    'tooltip' => null,
    'inset' => null,
])

@php
$classes = Flux::classes()
    ->add('w-10 h-8 flex items-center justify-center')
    ->add('in-data-flux-sidebar-collapsed-desktop:opacity-0')
    ->add('in-data-flux-sidebar-collapsed-desktop:absolute')
    ->add('in-data-flux-sidebar-collapsed-desktop:in-data-flux-sidebar-active:opacity-100')
    ->add($inset ? Flux::applyInset($inset, top: '-mt-2.5', right: '-me-2.5', bottom: '-mb-2.5', left: '-ms-2.5') : '')
    ;

$buttonClasses = Flux::classes()
    ->add('size-10 relative items-center font-medium justify-center gap-2 whitespace-nowrap disabled:opacity-75 dark:disabled:opacity-75 disabled:cursor-default disabled:pointer-events-none text-sm rounded-lg inline-flex  bg-transparent hover:bg-zinc-800/5 dark:hover:bg-white/15 text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-white')
    ->add('in-data-flux-sidebar-collapsed-desktop:cursor-e-resize rtl:in-data-flux-sidebar-collapsed-desktop:cursor-w-resize')
    ->add('[&[collapsible="mobile"]]:in-data-flux-sidebar-on-desktop:hidden')
    ->add('rtl:rotate-180')
    ;
@endphp

<ui-sidebar-toggle {{ $attributes->class($classes) }} data-flux-sidebar-collapse>
    <flux:tooltip position="{{ $tooltipPosition }}" :kbd="$tooltipKbd">
        <button type="button" class="{{ $buttonClasses }}">
            <svg class="text-zinc-500 dark:text-zinc-400" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M7.5 3.75V16.25M3.4375 16.25H16.5625C17.08 16.25 17.5 15.83 17.5 15.3125V4.6875C17.5 4.17 17.08 3.75 16.5625 3.75H3.4375C2.92 3.75 2.5 4.17 2.5 4.6875V15.3125C2.5 15.83 2.92 16.25 3.4375 16.25Z" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
        </button>
        <flux:tooltip.content :kbd="$tooltipKbd">
            <span
                x-data="{
                    label: '{{ __('Toggle sidebar') }}',
                    _obs: null,
                    init() {
                        const sidebar = document.querySelector('[data-flux-sidebar]');
                        if (!sidebar) return;
                        this._update(sidebar);
                        this._obs = new MutationObserver(() => this._update(sidebar));
                        this._obs.observe(sidebar, { attributes: true, attributeFilter: ['data-flux-sidebar-collapsed-desktop'] });
                    },
                    _update(el) {
                        this.label = el.hasAttribute('data-flux-sidebar-collapsed-desktop')
                            ? '{{ __('Expand sidebar') }}'
                            : '{{ __('Collapse sidebar') }}';
                    },
                    destroy() { this._obs?.disconnect(); }
                }"
                x-text="label"
            ></span>
        </flux:tooltip.content>
    </flux:tooltip>
</ui-sidebar-toggle>
