<div class="admin-loading-modal" data-global-loading hidden aria-live="polite" aria-busy="true">
    <div class="admin-loading-modal__dialog" role="status">
        <span class="admin-loading-modal__spinner" aria-hidden="true"></span>
        <span class="admin-loading-modal__copy"><strong>Mohon tunggu</strong><small data-global-loading-message>Memproses permintaan...</small></span>
    </div>
</div>

<dialog class="admin-link-dialog" data-rich-link-dialog aria-labelledby="admin-link-dialog-title" aria-describedby="admin-link-dialog-description">
    <form data-rich-link-form>
        <div class="admin-link-dialog__heading">
            <span class="admin-link-dialog__icon material-symbols-rounded" aria-hidden="true">link</span>
            <div><h2 id="admin-link-dialog-title">Tambahkan tautan</h2><p id="admin-link-dialog-description">Hubungkan teks ke halaman yang ingin dituju.</p></div>
        </div>
        <label class="admin-link-dialog__field"><span>Teks tautan</span><input name="label" type="text" maxlength="500" placeholder="Contoh: Baca selengkapnya" required></label>
        <label class="admin-link-dialog__field"><span>URL tujuan</span><input name="url" type="text" maxlength="2048" placeholder="https://contoh.com" required aria-describedby="admin-link-dialog-hint"><small id="admin-link-dialog-hint">Gunakan tautan HTTPS atau path halaman, misalnya /id/kontak.</small></label>
        <p class="admin-link-dialog__error" data-rich-link-error role="alert" hidden></p>
        <div class="admin-link-dialog__actions"><button class="admin-button admin-link-dialog__remove" type="button" data-rich-link-remove hidden>Hapus tautan</button><button class="admin-button" type="button" data-rich-link-cancel>Batal</button><button class="admin-button admin-button--primary" type="submit">Simpan tautan</button></div>
    </form>
</dialog>

<div class="admin-sweet-alert" data-sweet-alert hidden>
    <section class="admin-sweet-alert__dialog" role="alertdialog" aria-modal="true" aria-labelledby="admin-sweet-alert-title" aria-describedby="admin-sweet-alert-message">
        <span class="admin-sweet-alert__icon" aria-hidden="true">
            <svg class="admin-sweet-alert__symbol admin-sweet-alert__symbol--success" viewBox="0 0 48 48" fill="none"><path d="M11 24.5 20 33l17-18" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <svg class="admin-sweet-alert__symbol admin-sweet-alert__symbol--error" viewBox="0 0 48 48" fill="none"><path d="M15 15 33 33M33 15 15 33" stroke="currentColor" stroke-width="4" stroke-linecap="round"/></svg>
            <svg class="admin-sweet-alert__symbol admin-sweet-alert__symbol--info" viewBox="0 0 48 48" fill="none"><path d="M24 21v13M24 14h.01" stroke="currentColor" stroke-width="4" stroke-linecap="round"/></svg>
            <svg class="admin-sweet-alert__symbol admin-sweet-alert__symbol--warning" viewBox="0 0 48 48" fill="none"><path d="M24 11v17M24 36h.01" stroke="currentColor" stroke-width="4" stroke-linecap="round"/></svg>
        </span>
        <h2 id="admin-sweet-alert-title" data-sweet-alert-title>Berhasil</h2>
        <p id="admin-sweet-alert-message" data-sweet-alert-message></p>
        <div class="admin-sweet-alert__actions">
            <button class="admin-button" type="button" data-sweet-alert-cancel hidden>Batal</button>
            <button class="admin-button admin-button--primary" type="button" data-sweet-alert-confirm>Oke</button>
        </div>
    </section>
</div>
