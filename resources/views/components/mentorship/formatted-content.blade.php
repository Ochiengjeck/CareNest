@props(['content'])

<div {{ $attributes->merge(['class' => 'prose prose-zinc dark:prose-invert max-w-none']) }}>
    {!! Str::markdown($content ?? '', ['html_input' => 'escape', 'allow_unsafe_links' => false]) !!}
</div>
