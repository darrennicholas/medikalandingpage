@extends('admin.layout')
@section('title', 'Informasi Kontak')
@section('page-heading', 'Informasi Kontak')
@section('content')
<div class="admin-contact-layout" data-contact-editor>
    <form class="admin-panel admin-form admin-cms-form admin-contact-form" data-admin-form data-contact-form data-resource="contact-info" data-api-url="{{ url('/api/v1/admin/contact-info') }}" data-is-editing="1" data-redirect-url="{{ route('admin.content.index', 'contact-info') }}">
        @csrf
        <input type="hidden" name="contacts_present" value="1">
        <div class="admin-language-tabs" role="tablist" aria-label="Bahasa informasi kontak">
            <button type="button" class="is-active" data-language-tab="id">Bahasa Indonesia</button>
            <button type="button" data-language-tab="en">Bahasa Inggris</button>
            <button type="button" data-language-tab="zh">Mandarin</button>
        </div>
        <div class="admin-contact-list" data-contact-list>
            @foreach ($records as $index => $record)
                @include('admin.content.partials.contact-entry', ['record' => $record, 'index' => $index])
            @endforeach
        </div>
        <template data-contact-template>
            @include('admin.content.partials.contact-entry', ['record' => new \App\Models\ContactInfo, 'index' => '__INDEX__'])
        </template>
        <div class="admin-contact-toolbar">
            <button class="admin-button admin-button--soft" type="button" data-contact-add><span class="material-symbols-rounded" aria-hidden="true">add</span>Tambah informasi kontak</button>
        </div>
        <div class="admin-form__error" data-form-errors hidden></div>
        <div class="admin-form__actions"><button class="admin-button admin-button--primary" type="submit">Simpan Perubahan</button></div>
    </form>
    <aside class="admin-panel admin-contact-preview" data-contact-preview-list>
        <h2>Pratinjau Kontak</h2>
        <div class="admin-contact-preview__grid">
            @foreach ($records as $index => $record)
                @php($translation = $record->translations->firstWhere('lang', 'id'))
                <article class="admin-contact-preview__card" data-contact-preview-card="{{ $index }}">
                    <h3 data-contact-value="label">{{ $record->label ?: 'Informasi Kontak '.($index + 1) }}</h3>
                    <div><span>Telepon</span><strong data-contact-value="phone">{{ $record->phone ?: '—' }}</strong></div>
                    <div><span>Email</span><strong data-contact-value="email">{{ $record->email ?: '—' }}</strong></div>
                    <div><span>Alamat</span><strong data-contact-value="address">{{ $translation?->address ?: '—' }}</strong></div>
                    <div><span>Jam Operasional</span><strong data-contact-value="hours">{{ $translation?->office_hours ?: '—' }}</strong></div>
                </article>
            @endforeach
        </div>
    </aside>
</div>
@endsection
