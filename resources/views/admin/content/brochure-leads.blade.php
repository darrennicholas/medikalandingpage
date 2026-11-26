@extends('admin.layout')
@section('title', 'Permintaan Brosur')
@section('page-heading', 'Permintaan Brosur')
@section('breadcrumb', 'Konten Landing Page / Produk / Permintaan Brosur')
@section('page-description', 'Kelola permintaan brosur dari pengunjung website.')
@section('content')
        <section class="admin-panel" aria-labelledby="leads-heading">
            <div class="admin-panel__header"><div><h2 id="leads-heading">Permintaan Brosur</h2><p>{{ $leads->total() }} permintaan diterima</p></div></div>
            <div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Waktu</th><th>Produk</th><th>Nama</th><th>Email</th><th>Telepon</th><th>Perusahaan</th></tr></thead><tbody>
                @forelse ($leads as $lead)
                    <tr><td>{{ \Illuminate\Support\Carbon::parse($lead->created_at)->format('d/m/Y H:i') }}</td><td>{{ $lead->product_name ?: 'Produk #'.$lead->product_id }}</td><td>{{ $lead->name }}</td><td><a href="mailto:{{ $lead->email }}">{{ $lead->email }}</a></td><td>{{ $lead->phone }}</td><td>{{ $lead->company ?: '—' }}</td></tr>
                @empty<tr><td colspan="6" class="admin-empty">Belum ada permintaan brosur.</td></tr>@endforelse
            </tbody></table></div>
            @include('admin.partials.pagination', ['paginator' => $leads, 'label' => 'permintaan'])
        </section>
@endsection
