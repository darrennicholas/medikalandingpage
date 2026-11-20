@props(['src', 'alt' => '', 'sizes' => '100vw', 'loading' => 'lazy'])

<img {{ $attributes->merge(['src' => $src, 'alt' => $alt, 'loading' => $loading]) }} srcset="{{ $src }}?variant=thumbnail 640w, {{ $src }} 1600w" sizes="{{ $sizes }}">
