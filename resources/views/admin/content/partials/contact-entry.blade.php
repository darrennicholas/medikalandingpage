@php
    $contactEntryPrefix = 'contacts['.$index.']';
    $contactTranslation = fn (string $language) => $record->translations->firstWhere('lang', $language);
@endphp
<section class="admin-contact-entry" data-contact-entry data-contact-index="{{ $index }}">
    <header class="admin-contact-entry__header">
        <div><span class="admin-eyebrow">Saluran komunikasi</span><h2 data-contact-entry-title>Informasi Kontak {{ is_numeric($index) ? $index + 1 : '' }}</h2></div>
        <button class="admin-button admin-button--danger" type="button" data-contact-remove><span class="material-symbols-rounded" aria-hidden="true">delete</span>Hapus</button>
    </header>
    @if ($record->exists)
        <input type="hidden" name="{{ $contactEntryPrefix }}[id]" value="{{ $record->id }}">
        <input type="hidden" name="{{ $contactEntryPrefix }}[expected_version]" value="{{ app(\App\Services\ContentVersion::class)->token($record) }}">
    @endif
    <div class="admin-contact-section">
        <label class="admin-field"><span>Nama informasi kontak</span><input name="{{ $contactEntryPrefix }}[label]" maxlength="120" value="{{ old('contacts.'.$index.'.label', $record->label) }}" placeholder="Contoh: Kantor Pusat" data-contact-field="label"></label>
        <div class="admin-contact-fields">
            <label class="admin-field"><span>Nomor Telepon <b>*</b></span><input name="{{ $contactEntryPrefix }}[phone]" type="tel" maxlength="30" required value="{{ old('contacts.'.$index.'.phone', $record->phone) }}" placeholder="(021) 2252 7790" data-contact-field="phone"></label>
            <label class="admin-field"><span>Email <b>*</b></span><input name="{{ $contactEntryPrefix }}[email]" type="email" maxlength="150" required value="{{ old('contacts.'.$index.'.email', $record->email) }}" placeholder="sales@perusahaan.com" data-contact-field="email"></label>
        </div>
    </div>
    <div class="admin-contact-section">
        @foreach (['id' => 'Bahasa Indonesia', 'en' => 'Bahasa Inggris', 'zh' => 'Mandarin'] as $language => $languageLabel)
            @php($translation = $contactTranslation($language))
            <div class="admin-language-panel {{ $language !== 'id' ? 'is-hidden' : '' }}" data-language-panel="{{ $language }}">
                <label class="admin-field"><span>Alamat {{ $languageLabel }} @if ($language === 'id')<b>*</b>@endif</span><textarea name="{{ $contactEntryPrefix }}[translations][{{ $language }}][address]" maxlength="300" placeholder="Masukkan alamat kontak" @required($language === 'id') @if ($language === 'id') data-contact-field="address" @endif>{{ old('contacts.'.$index.'.translations.'.$language.'.address', $translation?->address) }}</textarea></label>
                <label class="admin-field"><span>Jam Operasional {{ $languageLabel }}</span><input name="{{ $contactEntryPrefix }}[translations][{{ $language }}][office_hours]" maxlength="150" value="{{ old('contacts.'.$index.'.translations.'.$language.'.office_hours', $translation?->office_hours) }}" placeholder="Senin–Jumat, 08.00–17.00 WIB" @if ($language === 'id') data-contact-field="hours" @endif></label>
            </div>
        @endforeach
    </div>
    <details class="admin-contact-location">
        <summary>Pengaturan lokasi peta (opsional)</summary>
        <label class="admin-field"><span>Link Google Maps</span><input name="{{ $contactEntryPrefix }}[maps_url]" type="url" maxlength="500" value="{{ old('contacts.'.$index.'.maps_url', $record->maps_url) }}" placeholder="https://maps.google.com/..."></label>
        <div class="admin-contact-coordinates">
            <label class="admin-field"><span>Latitude</span><input name="{{ $contactEntryPrefix }}[latitude]" type="number" step="any" min="-90" max="90" value="{{ old('contacts.'.$index.'.latitude', $record->latitude) }}"></label>
            <label class="admin-field"><span>Longitude</span><input name="{{ $contactEntryPrefix }}[longitude]" type="number" step="any" min="-180" max="180" value="{{ old('contacts.'.$index.'.longitude', $record->longitude) }}"></label>
        </div>
    </details>
</section>
