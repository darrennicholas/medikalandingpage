@php
    $currentLanguage = app()->getLocale();
    $availableLanguages = ['id' => 'Bahasa Indonesia', 'en' => 'English', 'zh' => '中文'];
    $languagePath = preg_replace('#^/(id|en|zh)(?=/|$)#', '', '/'.ltrim(request()->path(), '/')) ?: '/';
    $languageQuery = request()->getQueryString();
@endphp
<details class="language-switcher" data-language-switcher>
    <summary class="language-switcher__trigger" aria-label="{{ $currentLanguage === 'en' ? 'Change language' : 'Ubah bahasa' }}">
        <span class="material-symbols-rounded language-switcher__icon" aria-hidden="true">language</span>
        <span class="language-switcher__current">{{ $availableLanguages[$currentLanguage] ?? strtoupper($currentLanguage) }}</span>
        <span class="material-symbols-rounded language-switcher__chevron" aria-hidden="true">expand_more</span>
    </summary>
    <div class="language-switcher__menu" role="menu" aria-label="{{ $currentLanguage === 'en' ? 'Select language' : 'Pilih bahasa' }}">
        @foreach ($availableLanguages as $locale => $label)
            <a class="language-switcher__option {{ $locale === $currentLanguage ? 'is-active' : '' }}"
               href="{{ url('/'.$locale.$languagePath).($languageQuery ? '?'.$languageQuery : '') }}"
               hreflang="{{ $locale }}"
               lang="{{ $locale }}"
               role="menuitemradio"
               aria-checked="{{ $locale === $currentLanguage ? 'true' : 'false' }}"
               data-language-option="{{ $locale }}">{{ $label }}</a>
        @endforeach
    </div>
</details>
