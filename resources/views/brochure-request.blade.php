<!DOCTYPE html>
<html lang="{{ $language }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $language === 'en' ? 'Request brochure' : 'Permintaan brosur' }} | IMI Medika</title>
    @vite(['resources/css/app.css', 'resources/css/products.css'])
</head>
<body>
    @php($productName = $product->translations->firstWhere('lang', $language)?->name ?: $product->translations->firstWhere('lang', 'id')?->name)
    @php($copy = match ($language) {
        'en' => ['Back to products', 'View product brochure', 'Please provide your details so our sales team can contact you about this product.', 'Full name', 'Phone number', 'Company (optional)', 'I agree to be contacted by the sales team about this product.', 'Continue to brochure'],
        'zh' => ['返回产品', '查看产品手册', '请填写您的资料，以便销售团队就此产品与您联系。', '姓名', '电话号码', '公司（选填）', '我同意销售团队就此产品与我联系。', '继续查看手册'],
        default => ['Kembali ke produk', 'Lihat brosur produk', 'Isi data Anda agar tim sales dapat menghubungi Anda mengenai produk ini.', 'Nama lengkap', 'Nomor telepon', 'Perusahaan (opsional)', 'Saya setuju dihubungi oleh tim sales mengenai produk ini.', 'Lanjutkan ke brosur'],
    })
    <main class="brochure-request">
        <div class="brochure-request__card">
            <a class="brochure-request__back" href="{{ route('localized.products', ['locale' => $language]) }}">← {{ $copy[0] }}</a>
            <h1>{{ $copy[1] }}</h1>
            <p>{{ $productName }}</p>
            <p>{{ $copy[2] }}</p>
            @if ($errors->any())<div class="brochure-request__error" role="alert">{{ $errors->first() }}</div>@endif
            <form method="POST" action="{{ route('localized.brochure.submit', ['locale' => $language, 'product' => $product->id]) }}">
                @csrf
                <label>{{ $copy[3] }} <span>*</span><input name="name" value="{{ old('name') }}" autocomplete="name" maxlength="120" required></label>
                <label>Email <span>*</span><input type="email" name="email" value="{{ old('email') }}" autocomplete="email" maxlength="150" required></label>
                <label>{{ $copy[4] }} <span>*</span><input type="tel" name="phone" value="{{ old('phone') }}" autocomplete="tel" maxlength="30" required></label>
                <label>{{ $copy[5] }}<input name="company" value="{{ old('company') }}" autocomplete="organization" maxlength="150"></label>
                <label class="brochure-request__consent"><input type="checkbox" name="consent" value="1" required @checked(old('consent'))><span>{{ $copy[6] }}</span></label>
                <button type="submit">{{ $copy[7] }}</button>
            </form>
        </div>
    </main>
</body>
</html>
