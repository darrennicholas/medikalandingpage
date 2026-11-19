@extends('admin.layout')
@section('title', 'Informasi Kontak')
@section('page-heading', 'Informasi Kontak')
@section('content')
<div class="admin-contact-layout" data-contact-editor>
    <form class="admin-panel admin-form admin-cms-form admin-contact-form" data-admin-form data-resource="contact-info" data-api-url="{{ url('/api/v1/admin/contact-info') }}" data-is-editing="{{ $record->exists ? '1' : '0' }}" data-redirect-url="{{ route('admin.content.index', 'contact-info') }}">
        @include('admin.partials.record-version', ['record' => $record])
        @csrf
        <div class="admin-language-tabs" role="tablist"><button type="button" class="is-active" data-language-tab="id">Bahasa Indonesia</button><button type="button" data-language-tab="en">English</button><button type="button" data-language-tab="zh">中文</button></div>
        <section class="admin-contact-section">
        <h2>Saluran Komunikasi</h2>
        <div class="admin-contact-fields"><label class="admin-field"><span>Nomor Telepon <b>*</b></span><input name="phone" type="tel" maxlength="20" required value="{{ old('phone', $record->phone) }}" data-contact-preview="phone"></label>
        <label class="admin-field"><span>Email <b>*</b></span><input name="email" type="email" maxlength="150" required value="{{ old('email', $record->email) }}" data-contact-preview="email"></label></div>
        </section>
        <section class="admin-contact-section">
        <h2>Lokasi &amp; Jam Operasional</h2>
        @foreach (['id' => 'Bahasa Indonesia', 'en' => 'English', 'zh' => '中文'] as $language => $label)
            <div class="admin-language-panel {{ $language !== 'id' ? 'is-hidden' : '' }}" data-language-panel="{{ $language }}">
                <label class="admin-field"><span>Alamat {{ $label }} @if ($language === 'id')<b>*</b>@endif</span><textarea name="translations[{{ $language }}][address]" maxlength="300" @required($language === 'id') @if ($language === 'id') data-contact-preview="address" @endif>{{ old('translations.'.$language.'.address', $translations->get($language)?->address) }}</textarea></label>
                <label class="admin-field"><span>Jam Operasional {{ $label }}</span><input name="translations[{{ $language }}][office_hours]" maxlength="150" value="{{ old('translations.'.$language.'.office_hours', $translations->get($language)?->office_hours) }}" @if ($language === 'id') data-contact-preview="hours" @endif></label>
            </div>
        @endforeach
        </section>
        <section class="admin-contact-section">
        <h2>Peta &amp; Koordinat</h2>
        <label class="admin-field"><span>Link Google Maps</span><input name="maps_url" type="url" maxlength="500" value="{{ old('maps_url', $record->maps_url) }}"></label>
        <div class="admin-contact-coordinates"><label class="admin-field"><span>Latitude</span><input name="latitude" type="number" step="any" min="-90" max="90" value="{{ old('latitude', $record->latitude) }}"></label><label class="admin-field"><span>Longitude</span><input name="longitude" type="number" step="any" min="-180" max="180" value="{{ old('longitude', $record->longitude) }}"></label></div>
        </section>
        <div class="admin-form__error" data-form-errors hidden></div><div class="admin-form__actions"><button class="admin-button admin-button--primary" type="submit">Simpan Perubahan</button></div>
    </form>
    <aside class="admin-panel admin-contact-preview"><h2>Pratinjau Kontak</h2><div class="admin-contact-preview__card"><h3>Hubungi Kami</h3>@foreach (['phone' => 'Telepon', 'email' => 'Email', 'address' => 'Alamat', 'hours' => 'Jam Operasional'] as $key => $label)<div><span>{{ $label }}</span><strong data-contact-value="{{ $key }}">{{ match ($key) { 'phone' => $record->phone, 'email' => $record->email, 'address' => $translations->get('id')?->address, 'hours' => $translations->get('id')?->office_hours } ?: '—' }}</strong></div>@endforeach</div></aside>
</div>
@endsection
