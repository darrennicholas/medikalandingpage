<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Certificate;
use App\Models\CompanySummary;
use App\Models\ContactInfo;
use App\Models\ContentBlock;
use App\Models\Documentation;
use App\Models\HeroSlide;
use App\Models\Media;
use App\Models\MissionItem;
use App\Models\SeoMeta;
use App\Models\SocialLink;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use RuntimeException;

class CmsInitialContentSeeder extends Seeder
{
    public function run(): void
    {
        $aboutImage = $this->media('images/gambartentangmedika.jpeg', 'Tim PT Interskala Medika Indonesia', 'PT Interskala Medika Indonesia team');
        $coverageMap = $this->media('images/coverage-map.jpg', 'Peta jangkauan PT Interskala Medika Indonesia', 'PT Interskala Medika Indonesia coverage map');

        $hero = HeroSlide::query()->firstOrCreate(
            ['display_order' => 1],
            ['media_id' => $aboutImage->id, 'cta_url' => '/id/kontak', 'status' => 'published'],
        );
        $hero->translations()->firstOrCreate(['lang' => 'id'], [
            'title' => 'PT INTERSKALA MEDIKA INDONESIA',
            'subtitle' => 'WE BRING THE FUTURE HEALTHCARE',
            'cta_label' => 'Hubungi Kami',
        ]);
        $hero->translations()->firstOrCreate(['lang' => 'en'], [
            'title' => 'PT INTERSKALA MEDIKA INDONESIA',
            'subtitle' => 'WE BRING THE FUTURE HEALTHCARE',
            'cta_label' => 'Contact Us',
        ]);

        $this->contentBlock('home_coverage', $coverageMap, [
            'id' => [
                'title' => 'Jangkauan Kami',
                'subtitle' => 'Jaringan Penjualan',
                'body' => 'PT Interskala Medika Indonesia memiliki jaringan distribusi yang mencakup seluruh wilayah Indonesia, dari Sabang sampai Merauke. Kami berkomitmen untuk menyediakan alat kesehatan berkualitas ke setiap pelosok negeri.',
            ],
            'en' => [
                'title' => 'Our Reach',
                'subtitle' => 'Sales Network',
                'body' => 'PT Interskala Medika Indonesia has a distribution network covering Indonesia, from Sabang to Merauke. We are committed to delivering quality medical devices throughout the country.',
            ],
        ]);
        $this->contentBlock('home_cta', null, [
            'id' => [
                'title' => 'Siap Membantu Kebutuhan Alat Kesehatan Anda',
                'subtitle' => 'Jangan ragu untuk menghubungi kami untuk informasi lebih lanjut',
                'cta_label' => 'Hubungi Kami!',
            ],
            'en' => [
                'title' => 'Ready to Help With Your Medical Equipment Needs',
                'subtitle' => 'Please contact us for further information',
                'cta_label' => 'Contact Us',
            ],
        ], '/id/kontak');
        $this->contentBlock('about_company', $aboutImage, [
            'id' => [
                'title' => 'Tentang Kami',
                'subtitle' => 'IMPORTIR | DISTRIBUTOR | MANUFAKTUR ALAT KESEHATAN',
                'body' => '<p>PT Interskala Medika Indonesia adalah perusahaan impor, distribusi, dan manufaktur alat kesehatan, khususnya produk diagnostik dan in vitro (IVD). Dengan sertifikasi CE dan ISO, kami berkomitmen menghadirkan produk aman, berkualitas, dan sesuai standar internasional untuk mendukung pelayanan kesehatan yang lebih baik.</p><p>Kami terus berinovasi dalam teknologi, efisiensi, dan aksesibilitas, serta didukung oleh pengalaman, jaringan distribusi luas, dan tim profesional. PT Interskala Medika Indonesia siap menjadi mitra strategis bagi institusi kesehatan, laboratorium, dan penyedia layanan medis di seluruh Indonesia.</p>',
            ],
            'en' => [
                'title' => 'About Us',
                'subtitle' => 'IMPORTER | DISTRIBUTOR | MEDICAL DEVICE MANUFACTURER',
                'body' => '<p>PT Interskala Medika Indonesia imports, distributes, and manufactures medical devices, especially diagnostic and in vitro (IVD) products. With CE and ISO certifications, we are committed to providing safe, high-quality products that meet international standards and support better healthcare.</p><p>We keep innovating in technology, efficiency, and accessibility, backed by experience, an extensive distribution network, and a professional team. PT Interskala Medika Indonesia is ready to be a strategic partner for healthcare institutions, laboratories, and medical providers throughout Indonesia.</p>',
            ],
        ]);
        $this->contentBlock('about_vision', null, [
            'id' => [
                'title' => 'Visi',
                'body' => 'Menjadi mitra strategis terdepan dalam menghadirkan solusi alat kesehatan berstandar global bagi masa depan kesehatan Indonesia yang lebih andal dan berkelanjutan.',
            ],
            'en' => [
                'title' => 'Vision',
                'body' => 'To be a leading strategic partner in providing globally standardized medical device solutions for a more reliable and sustainable healthcare future in Indonesia.',
            ],
        ]);
        $this->contentBlock('footer_about', null, [
            'id' => [
                'title' => 'PT Interskala Medika Indonesia',
                'body' => 'Importir, distributor, dan manufaktur alat kesehatan yang menyediakan solusi diagnostik berkualitas untuk Indonesia.',
            ],
            'en' => [
                'title' => 'PT Interskala Medika Indonesia',
                'body' => 'Importer, distributor, and medical device manufacturer providing quality diagnostic solutions across Indonesia.',
            ],
        ]);
        $this->contentBlock('career_intro', null, [
            'id' => [
                'title' => 'Karir di Interskala Medika',
                'body' => 'Bergabunglah dengan tim kami dan ikut mendukung layanan kesehatan Indonesia.',
            ],
            'en' => [
                'title' => 'Careers at Interskala Medika',
                'body' => 'Join our team and help support healthcare across Indonesia.',
            ],
        ]);

        $summary = CompanySummary::query()->first();
        if ($summary === null) {
            CompanySummary::query()->create([
                'established_year' => 2021,
                'partner_count' => 5000,
                'province_count' => 34,
            ]);
        }

        $missions = [
            [
                'id' => 'Menghadirkan instrumen medis berkualitas tinggi yang memenuhi standar internasional demi pelayanan kesehatan yang presisi.',
                'en' => 'Provide high-quality medical instruments that meet international standards for precise healthcare.',
            ],
            [
                'id' => 'Mengembangkan solusi diagnostik yang inovatif dan kompetitif untuk memenuhi kebutuhan fasilitas kesehatan secara efektif.',
                'en' => 'Develop innovative and competitive diagnostic solutions that meet healthcare facility needs effectively.',
            ],
            [
                'id' => 'Membangun relasi jangka panjang dengan pelanggan melalui integritas, profesionalisme, dan layanan yang berkelanjutan.',
                'en' => 'Build long-term customer relationships through integrity, professionalism, and sustainable service.',
            ],
        ];
        foreach ($missions as $index => $mission) {
            $item = MissionItem::query()->whereHas('translations', fn ($query) => $query->where('lang', 'id')->where('text', $mission['id']))->first();
            if ($item === null) {
                $item = MissionItem::query()->create(['display_order' => $index + 1, 'status' => 'published']);
            }
            $item->translations()->firstOrCreate(['lang' => 'id'], ['text' => $mission['id']]);
            $item->translations()->firstOrCreate(['lang' => 'en'], ['text' => $mission['en']]);
        }

        $contact = ContactInfo::query()->first();
        if ($contact === null) {
            $contact = ContactInfo::query()->create([
                'phone' => '(021) 2252 7790',
                'email' => 'sales@imimedika.com',
                'maps_url' => 'https://www.google.com/maps?q=PT+Interskala+Medika+Indonesia+Green+Sedayu+Bizpark+Kalideres',
            ]);
        }
        $contact->translations()->firstOrCreate(['lang' => 'id'], [
            'address' => 'Jalan Daan Mogot KM 18, Green Sedayu Biz Park Blok DM 9 Nomor 62, Kalideres, Jakarta Barat 11840',
            'office_hours' => 'Senin–Jumat, 08.00–17.00 WIB',
        ]);
        $contact->translations()->firstOrCreate(['lang' => 'en'], [
            'address' => 'Jalan Daan Mogot KM 18, Green Sedayu Biz Park, Block DM 9 No. 62, Kalideres, West Jakarta 11840',
            'office_hours' => 'Monday–Friday, 08:00–17:00 WIB',
        ]);

        $socialLinks = [
            ['platform' => 'Instagram', 'url' => 'https://www.instagram.com/medikainterskala/'],
            ['platform' => 'LinkedIn', 'url' => 'https://www.linkedin.com/company/96334258/'],
            ['platform' => 'TikTok', 'url' => 'https://www.tiktok.com/@medikainterskala'],
        ];
        foreach ($socialLinks as $index => $socialLink) {
            SocialLink::query()->firstOrCreate(
                ['url' => $socialLink['url']],
                ['platform' => $socialLink['platform'], 'display_order' => $index + 1, 'status' => 'published'],
            );
        }

        $brandAssets = [
            'Indocheck' => 'images/brands/indocheck.png',
            'G Scientific' => 'images/brands/g-scientific.png',
            'Diagnose' => 'images/brands/diagnose.png',
            'Vitrogen' => 'images/brands/vitrogen.png',
            'Genomic' => 'images/brands/genomic.png',
            'MKCells' => 'images/brands/mkcells.png',
            'DR Plus' => 'images/brands/dr-plus.png',
            'IS' => 'images/brands/is.png',
            'Logix' => 'images/brands/logix.png',
            'Tamatech' => 'images/brands/tamatech.png',
            'Medicheck' => 'images/brands/medicheck.png',
        ];
        foreach ($brandAssets as $index => $brandAsset) {
            $media = $this->media($brandAsset, $index, $index);
            Brand::query()->firstOrCreate(
                ['name' => $index],
                ['logo_media_id' => $media->id, 'display_order' => array_search($index, array_keys($brandAssets), true) + 1, 'status' => 'published'],
            );
        }

        $certificates = [
            ['title' => 'Cara Distribusi Alat Kesehatan yang Baik', 'image' => 'images/cdakb.png', 'issuer' => 'KEMENKES'],
            ['title' => 'ISO 9001:2015', 'image' => 'images/iso-9001.png', 'issuer' => 'TERAKREDITASI'],
            ['title' => 'CDAKB', 'image' => 'images/cdakb.png', 'issuer' => 'KEMENTERIAN KESEHATAN'],
            ['title' => 'ISO 9001:2015 TUV Nord', 'image' => 'images/iso-9001.png', 'issuer' => 'TUV NORD'],
            ['title' => 'Tingkat Komponen Dalam Negeri', 'image' => 'images/tkdn.png', 'issuer' => 'KEMENPERIN'],
            ['title' => 'CDAKB Kemenkes RI', 'image' => 'images/cdakb.png', 'issuer' => 'KEMENKES RI'],
        ];
        foreach ($certificates as $index => $certificateData) {
            $media = $this->media($certificateData['image'], $certificateData['title'], $certificateData['title']);
            $certificate = Certificate::query()->whereHas('translations', fn ($query) => $query->where('lang', 'id')->where('title', $certificateData['title']))->first();
            if ($certificate === null) {
                $certificate = Certificate::query()->create([
                    'media_id' => $media->id,
                    'issued_year' => null,
                    'display_order' => $index + 1,
                    'status' => 'published',
                ]);
            }
            $certificate->translations()->firstOrCreate(['lang' => 'id'], [
                'title' => $certificateData['title'],
                'description' => $certificateData['issuer'],
            ]);
            $certificate->translations()->firstOrCreate(['lang' => 'en'], [
                'title' => $certificateData['title'],
                'description' => $certificateData['issuer'],
            ]);
        }

        $documentationMedia = $this->media(
            'images/hospital-expo-2026.jpg',
            'Tim Interskala Medika Indonesia dan Interskala Sehat Sejahtera di Hospital Expo 2026',
            'Interskala Medika Indonesia and Interskala Sehat Sejahtera team at Hospital Expo 2026',
        );
        $activity = Documentation::query()->where('type', 'activity')->whereDate('event_date', '2026-10-21')->first();
        if ($activity === null) {
            $activity = Documentation::query()->create([
                'type' => 'activity',
                'cover_media_id' => $documentationMedia->id,
                'event_date' => '2026-10-21',
                'status' => 'published',
            ]);
        }
        $activity->translations()->firstOrCreate(['lang' => 'id'], [
            'title' => 'Interskala Medika Indonesia Hadir di Hospital Expo 2026',
            'slug' => 'interskala-medika-hospital-expo-2026',
            'excerpt' => 'Memperkenalkan solusi alat kesehatan, memperluas koneksi industri, dan memperkuat komitmen sebagai mitra terpercaya dalam layanan kesehatan.',
            'body' => 'Interskala Medika Indonesia dan Interskala Sehat Sejahtera berpartisipasi dalam Indonesia International Hospital Expo 2026 pada 7–10 Oktober 2026 di Indonesia Convention Exhibition (ICE) BSD City, Tangerang. Melalui pameran ini, kami memperkenalkan produk unggulan dan solusi alat kesehatan serta bertemu distributor, profesional kesehatan, dan pelaku industri.',
        ]);
        $activity->translations()->firstOrCreate(['lang' => 'en'], [
            'title' => 'Interskala Medika Indonesia at Hospital Expo 2026',
            'slug' => 'interskala-medika-hospital-expo-2026',
            'excerpt' => 'Introducing medical device solutions, expanding industry connections, and strengthening our commitment as a trusted healthcare partner.',
            'body' => 'Interskala Medika Indonesia and Interskala Sehat Sejahtera participated in Indonesia International Hospital Expo 2026 from 7–10 October 2026 at Indonesia Convention Exhibition (ICE) BSD City, Tangerang. We introduced our products and medical device solutions and met distributors, healthcare professionals, and industry partners.',
        ]);

        $seoDefaults = [
            'home' => [
                'id' => ['PT Interskala Medika Indonesia', 'PT Interskala Medika Indonesia melayani kebutuhan alat kesehatan berkualitas di seluruh Indonesia.'],
                'en' => ['PT Interskala Medika Indonesia', 'PT Interskala Medika Indonesia provides quality medical equipment across Indonesia.'],
            ],
            'about' => [
                'id' => ['Tentang Kami - Interskala Medika Indonesia', 'Kenali visi, misi, pengalaman, dan sertifikasi PT Interskala Medika Indonesia.'],
                'en' => ['About Us - Interskala Medika Indonesia', 'Learn about the vision, mission, experience, and certifications of PT Interskala Medika Indonesia.'],
            ],
            'products' => [
                'id' => ['Produk Kami - Interskala Medika Indonesia', 'Jelajahi produk alat kesehatan dan diagnostik PT Interskala Medika Indonesia.'],
                'en' => ['Our Products - Interskala Medika Indonesia', 'Explore medical equipment and diagnostic products from PT Interskala Medika Indonesia.'],
            ],
            'documentation' => [
                'id' => ['Dokumentasi - Interskala Medika Indonesia', 'Artikel dan kegiatan terbaru PT Interskala Medika Indonesia.'],
                'en' => ['News & Activities - Interskala Medika Indonesia', 'Read the latest articles and activities from PT Interskala Medika Indonesia.'],
            ],
            'careers' => [
                'id' => ['Karir - Interskala Medika Indonesia', 'Temukan peluang karir di PT Interskala Medika Indonesia.'],
                'en' => ['Careers - Interskala Medika Indonesia', 'Explore career opportunities at PT Interskala Medika Indonesia.'],
            ],
            'contact' => [
                'id' => ['Kontak - Interskala Medika Indonesia', 'Hubungi PT Interskala Medika Indonesia untuk informasi produk dan layanan.'],
                'en' => ['Contact - Interskala Medika Indonesia', 'Contact PT Interskala Medika Indonesia for product and service information.'],
            ],
        ];
        foreach ($seoDefaults as $pageKey => $translations) {
            foreach ($translations as $language => [$title, $description]) {
                SeoMeta::query()->firstOrCreate(
                    ['page_key' => $pageKey, 'lang' => $language],
                    [
                        'meta_title' => $title,
                        'meta_description' => $description,
                        'canonical_url' => null,
                        'og_media_id' => $aboutImage->id,
                        'robots_index' => true,
                    ],
                );
            }
        }
    }

    private function contentBlock(string $key, ?Media $media, array $translations, ?string $ctaUrl = null): ContentBlock
    {
        $block = ContentBlock::query()->firstOrCreate(
            ['block_key' => $key],
            ['media_id' => $media?->id, 'cta_url' => $ctaUrl, 'status' => 'published'],
        );
        foreach ($translations as $language => $translation) {
            $block->translations()->firstOrCreate(['lang' => $language], $translation);
        }

        return $block;
    }

    private function media(string $path, string $altId, string $altEn): Media
    {
        $fullPath = public_path($path);
        if (! File::exists($fullPath)) {
            throw new RuntimeException("Initial CMS media is missing: {$path}");
        }

        $image = @getimagesize($fullPath);
        if ($image === false) {
            throw new RuntimeException("Initial CMS media is not a valid image: {$path}");
        }

        $fileName = basename($path);

        return Media::query()->firstOrCreate(
            ['path' => $path],
            [
                'file_name' => $fileName,
                'original_name' => $fileName,
                'mime_type' => $image['mime'],
                'size_bytes' => File::size($fullPath),
                'width' => $image[0],
                'height' => $image[1],
                'alt_text_id' => (string) $altId,
                'alt_text_en' => (string) $altEn,
            ],
        );
    }
}
