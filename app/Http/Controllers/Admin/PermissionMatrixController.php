<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PermissionMatrixController extends Controller
{
    public function index(): View
    {
        return view('admin.permissions.index', [
            'modules' => self::modules(),
            'adminRole' => Role::query()->where('name', 'admin')->firstOrFail(),
            'roles' => Role::query()->whereIn('name', ['superadmin', 'admin'])->withCount('users')->get(),
            'defaultPermissions' => self::defaultPermissions(),
        ]);
    }

    /** @return array<string, list<string>> */
    public static function defaultPermissions(): array
    {
        $defaults = [];
        foreach (self::modules() as $key => $module) {
            $defaults[$key] = $module['actions'];
        }

        return $defaults;
    }

    public function update(Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $role = Role::query()->where('name', 'admin')->firstOrFail();
        $validated = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['array'],
            'permissions.*.*' => ['in:view,create,update,delete,publish'],
        ]);
        $permissions = [];

        foreach (self::modules() as $key => $module) {
            $selected = array_values(array_intersect($validated['permissions'][$key] ?? [], $module['actions']));
            if ($selected !== []) {
                $permissions[$key] = $selected;
            }
        }

        $old = $role->permissions ?? [];
        $role->update(['permissions' => $permissions]);
        $activityLogger->log($request->user(), 'update', Role::class, $role->id, ['permissions' => $old], ['permissions' => $permissions]);

        return redirect()->route('admin.permissions.index')->with('status', 'Hak akses Admin berhasil disimpan.');
    }

    /** @return array<string, array{label: string, group: string, actions: list<string>}> */
    public static function modules(): array
    {
        $content = ['view', 'create', 'update', 'delete', 'publish'];
        $edit = ['view', 'update', 'publish'];

        return [
            'dashboard' => ['label' => 'Dashboard Statistik Utama', 'group' => 'Dashboard', 'actions' => ['view']],
            'home_hero_banner' => ['label' => 'Hero / Main Banner', 'group' => 'Konten Landing Page', 'actions' => $content],
            'home_coverage' => ['label' => 'Jangkauan Kami (Peta & Coverage)', 'group' => 'Konten Landing Page', 'actions' => $edit],
            'home_cta' => ['label' => 'CTA Hubungi Kami (Section Lead)', 'group' => 'Konten Landing Page', 'actions' => $edit],
            'home_brands' => ['label' => 'Brand Kami (Mitra & Logo Klien)', 'group' => 'Konten Landing Page', 'actions' => $content],
            'about_company' => ['label' => 'Tentang Perusahaan (Profil Korporasi)', 'group' => 'Konten Landing Page', 'actions' => $edit],
            'about_summary' => ['label' => 'Summary Nilai & Pencapaian', 'group' => 'Konten Landing Page', 'actions' => $edit],
            'about_mission' => ['label' => 'Visi & Misi', 'group' => 'Konten Landing Page', 'actions' => $content],
            'about_certificates' => ['label' => 'Sertifikat & Penghargaan', 'group' => 'Konten Landing Page', 'actions' => $content],
            'products_categories' => ['label' => 'Pengaturan Kategori', 'group' => 'Konten Landing Page', 'actions' => $content],
            'products_catalog' => ['label' => 'Katalog Produk & Layanan', 'group' => 'Konten Landing Page', 'actions' => $content],
            'documentation' => ['label' => 'Dokumentasi (Artikel & Kegiatan)', 'group' => 'Konten Landing Page', 'actions' => $content],
            'careers' => ['label' => 'Karir (Lowongan Kerja)', 'group' => 'Konten Landing Page', 'actions' => $content],
            'contacts' => ['label' => 'Informasi Kontak & Helpdesk', 'group' => 'Konten Landing Page', 'actions' => $edit],
            'footer' => ['label' => 'Sosial Media & Struktur Footer', 'group' => 'Konten Landing Page', 'actions' => $content],
            'media' => ['label' => 'Media Library (File & Gambar)', 'group' => 'Media & Pengaturan SEO', 'actions' => ['view', 'create', 'update', 'delete']],
            'seo' => ['label' => 'Pengaturan SEO (Meta, Sitemap, OpenGraph)', 'group' => 'Media & Pengaturan SEO', 'actions' => $edit],
            'users' => ['label' => 'Manajemen User', 'group' => 'Pengaturan Sistem', 'actions' => ['view', 'create', 'update', 'delete']],
            'permissions' => ['label' => 'Hak Akses (Permission)', 'group' => 'Pengaturan Sistem', 'actions' => ['view', 'update']],
            'activity_logs' => ['label' => 'Log Aktivitas (Audit Trail)', 'group' => 'Pengaturan Sistem', 'actions' => ['view']],
        ];
    }
}
