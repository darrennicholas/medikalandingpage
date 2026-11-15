@extends('admin.layout')
@section('title', 'Summary Perusahaan')
@section('page-heading', 'Summary Perusahaan')
@section('breadcrumb', 'Konten Landing Page  /  Tentang  /  Summary')
@section('content')
    <section class="admin-summary-layout">
        <form class="admin-panel admin-summary-form" data-admin-form data-resource="company-summary" data-api-url="{{ $apiUrl }}" data-is-editing="{{ $record->exists ? '1' : '0' }}" data-redirect-url="{{ $redirectUrl }}">
            @csrf
            <div class="admin-summary-form__heading">
                <span class="material-symbols-rounded" aria-hidden="true">analytics</span>
                <div><h2>Formulir Ringkasan Metrik</h2><p>Perbarui angka utama perusahaan yang tampil pada halaman Tentang.</p></div>
            </div>
            <label class="admin-field"><span>Tahun Berdiri <b>*</b></span><input type="number" name="established_year" value="{{ old('established_year', $record->established_year) }}" min="1900" max="{{ now()->year }}" inputmode="numeric" required></label>
            <label class="admin-field"><span>Jumlah Mitra Jejaring <b>*</b></span><input type="number" name="partner_count" value="{{ old('partner_count', $record->partner_count) }}" min="0" inputmode="numeric" required></label>
            <label class="admin-field"><span>Jumlah Jangkauan Provinsi <b>*</b></span><input type="number" name="province_count" value="{{ old('province_count', $record->province_count) }}" min="0" max="38" inputmode="numeric" required></label>
            <div class="admin-form__error" data-form-errors hidden></div>
            <div class="admin-summary-form__actions"><button class="admin-button" type="reset">Atur Ulang</button><button class="admin-button admin-button--primary" type="submit"><span class="material-symbols-rounded" aria-hidden="true">save</span>Simpan Perubahan</button></div>
        </form>

        <aside class="admin-panel admin-summary-preview">
            <div class="admin-panel__header"><h2>Pratinjau Metrik</h2></div>
            <article class="admin-summary-metric"><span>Lama Berdiri Perusahaan</span><strong><span data-summary-years>{{ $record->established_year ? max(now()->year - $record->established_year, 0) : '—' }}</span><small> Tahun</small></strong><p>Pengalaman melayani sejak <span data-summary-year>{{ $record->established_year ?: '—' }}</span></p></article>
            <article class="admin-summary-metric"><span>Total Mitra Faskes</span><strong><span data-summary-partners>{{ $record->partner_count ?? '—' }}</span><small> Mitra</small></strong><p>Rumah sakit, klinik, dan laboratorium</p></article>
            <article class="admin-summary-metric"><span>Jangkauan Wilayah</span><strong><span data-summary-provinces>{{ $record->province_count ?? '—' }}</span><small> / 38 Provinsi</small></strong><p>Cakupan layanan di Indonesia</p></article>
        </aside>
    </section>
    <script>
        document.querySelectorAll('.admin-summary-form input').forEach((field) => field.addEventListener('input', () => {
            const form = field.form;
            const year = Number(form.elements.established_year.value);
            const partners = form.elements.partner_count.value;
            const provinces = form.elements.province_count.value;
            document.querySelector('[data-summary-years]').textContent = year ? Math.max(new Date().getFullYear() - year, 0) : '—';
            document.querySelector('[data-summary-year]').textContent = year || '—';
            document.querySelector('[data-summary-partners]').textContent = partners || '—';
            document.querySelector('[data-summary-provinces]').textContent = provinces || '—';
        }));
    </script>
@endsection
