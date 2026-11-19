<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;

class ContentVersion
{
    public function token(Model $record): string
    {
        $fresh = $record->newQuery()->whereKey($record->getKey())->firstOrFail();
        $attributes = $fresh->getRawOriginal();
        ksort($attributes);
        $translations = method_exists($fresh, 'translations')
            ? $fresh->translations()->orderBy('lang')->get()->map(fn (Model $translation): array => $translation->getRawOriginal())->all()
            : [];
        $gallery = method_exists($fresh, 'gallery')
            ? $fresh->gallery()->orderBy('media.id')->get()->map(fn (Model $media): array => $media->pivot->getRawOriginal())->all()
            : [];

        return hash('sha256', json_encode([$attributes, $translations, $gallery], JSON_THROW_ON_ERROR));
    }
}
