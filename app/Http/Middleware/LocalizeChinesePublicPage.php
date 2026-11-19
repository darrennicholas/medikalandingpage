<?php

namespace App\Http\Middleware;

use Closure;
use DOMDocument;
use DOMNode;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LocalizeChinesePublicPage
{
    private const TRANSLATIONS = [
        'Beranda' => '首页',
        'Bahasa Indonesia' => '印度尼西亚语',
        'English' => '英语',
        'Ubah bahasa' => '切换语言',
        'Pilih bahasa' => '选择语言',
        'PT Interskala Medika Indonesia, beranda' => 'PT Interskala Medika Indonesia，首页',
        'PT Interskala Medika Indonesia, home' => 'PT Interskala Medika Indonesia，首页',
        'IMI MEDIKA MEDIA' => 'IMI MEDIKA 资讯',
        'IMI Medika Media' => 'IMI Medika 资讯',
        'Artikel & Berita' => '文章与新闻',
        'Articles & News' => '文章与新闻',
        'Tambah' => '添加',
        'Tambah Artikel' => '添加文章',
        'Belum Ada Artikel' => '暂无文章',
        'Tambah Berita' => '添加新闻',
        'Belum Ada Berita' => '暂无新闻',
        'Daftar Artikel' => '文章列表',
        'Cari artikel' => '搜索文章',
        'Cari berita' => '搜索新闻',
        'Filter berita berdasarkan tahun' => '按年份筛选新闻',
        'Urutkan berita' => '新闻排序',
        'Terapkan' => '应用',
        'Semua tahun' => '所有年份',
        'No open positions yet' => '目前暂无职位空缺',
        'Belum ada lowongan yang dibuka' => '目前暂无职位空缺',
        'Please check back later for new opportunities with our team.' => '请稍后再来查看我们的最新职位。',
        'Kunjungi halaman ini kembali untuk melihat kesempatan terbaru bersama tim kami.' => '请稍后再来查看我们团队的最新机会。',
        'Open' => '招聘中',
        'View details' => '查看详情',
        'Closes' => '截止日期',
        'Photo gallery' => '图片集',
        'Galeri foto' => '图片集',
        'Enlarge image' => '放大图片',
        'Perbesar gambar' => '放大图片',
        'Back to careers' => '返回招聘页面',
        'Kembali ke Karir' => '返回招聘页面',
        'Back to products' => '返回产品列表',
        'Product' => '产品',
        'Product document' => '产品资料',
        'Dokumen produk' => '产品资料',
        'View brochure PDF' => '查看产品手册 PDF',
        'Lihat brosur PDF' => '查看产品手册 PDF',
        'Product details' => '产品详情',
        'Detail produk' => '产品详情',
        'Description' => '产品描述',
        'Deskripsi' => '产品描述',
        'Product information' => '产品信息',
        'Informasi Produk' => '产品信息',
        'Requirements' => '任职要求',
        'Persyaratan' => '任职要求',
        'Apply' => '申请职位',
        'Lamar' => '申请职位',
        'Our location' => '公司位置',
        'Lokasi Kami' => '公司位置',
        'Company location map' => '公司位置地图',
        'Interactive map of the company location' => '公司位置交互地图',
        'Open directions' => '查看路线',
        'Buka petunjuk arah' => '查看路线',
        'Buka petunjuk arah ↗' => '查看路线 ↗',
        'Operating Hours' => '营业时间',
        'Jam Operasional' => '营业时间',
        'Open location map' => '打开位置地图',
        'Buka lokasi di peta' => '在地图中打开位置',
        'Ubah bahasa' => '切换语言',
        'Change language' => '切换语言',
        'Select language' => '选择语言',
        'WE BRING THE FUTURE HEALTH CARE' => '携手开创医疗未来',
        'WE BRING THE FUTURE HEALTHCARE' => '携手开创医疗未来',
        'Molecular' => '分子诊断',
        'Rapid Test (Master)' => '快速检测（主分类）',
        'Point of Care Testing (POCT)' => '即时检测（POCT）',
        'Medical Instruments' => '医疗仪器',
        'Medical Consumables' => '医疗耗材',
        'Rapid Test' => '快速检测',
        'Product Categories' => '产品类别',
        'All Products' => '所有产品',
        'Search products...' => '搜索产品…',
        'Cari produk...' => '搜索产品…',
        'Cari produk' => '搜索产品',
        'Reset Filter' => '重置筛选',
        'Filter kategori produk' => '筛选产品类别',
        'Filter subkategori produk' => '筛选产品子类别',
        'Pilih kategori produk' => '选择产品类别',
        'Infectious Disease: HIV Series' => '传染病：HIV 系列',
        'Infectious Disease: Hepatitis' => '传染病：肝炎',
        'Infectious Disease: Tropical' => '传染病：热带病',
        'Infeksius HIV Series' => '传染病：HIV 系列',
        'Infeksius Hepatitis' => '传染病：肝炎',
        'Infeksius Tropical' => '传染病：热带病',
        'Tumor Marker' => '肿瘤标志物',
        'Drugs of Abuse' => '毒品滥用检测',
        'Filter Tips and More' => '滤芯及其他耗材',
        'Filtertips dkk' => '滤芯及其他耗材',
        'Blood Vacuum tube' => '真空采血管',
        'Urine Analyzer and Strips' => '尿液分析仪及试纸',
        'Chemistry Analyzer and Reagent' => '生化分析仪及试剂',
        'Electrolyte Analyzer and Reagent' => '电解质分析仪及试剂',
        'Hematology Analyzer and Reagent' => '血液分析仪及试剂',
        'Cholesterol' => '胆固醇',
        'Uric Acid' => '尿酸',
        'Glucose' => '葡萄糖',
        'Lipid' => '血脂',
        'Home' => '首页',
        'Tentang' => '关于我们',
        'About' => '关于我们',
        'Produk' => '产品',
        'Products' => '产品',
        'Dokumentasi' => '资讯与活动',
        'Documentation' => '资讯与活动',
        'DOKUMENTASI' => '资讯与活动',
        'DOCUMENTATION' => '资讯与活动',
        'Karir' => '职业发展',
        'Careers' => '职业发展',
        'Kontak' => '联系我们',
        'Contact' => '联系我们',
        'Open location map' => '打开位置地图',
        'Buka lokasi di peta' => '在地图中打开位置',
        'Operating Hours' => '营业时间',
        'Jam Operasional' => '营业时间',
        'Our location' => '公司位置',
        'Lokasi Kami' => '公司位置',
        'Open directions' => '查看路线',
        'Buka petunjuk arah' => '查看路线',
        'Follow Us' => '关注我们',
        'Apply' => '应用',
        'Terapkan' => '应用',
        'All years' => '所有年份',
        'Semua tahun' => '所有年份',
        'Sort' => '排序',
        'Order' => '顺序',
        'Urutan' => '顺序',
        'Oldest' => '最早',
        'Back' => '返回',
        'Daftar Artikel' => '文章列表',
        'Explore health articles from IMI Medika.' => '阅读 IMI Medika 医疗健康文章。',
        'Follow the latest updates and activities from IMI Medika.' => '关注 IMI Medika 的最新动态与活动。',
        'Ikuti perkembangan terbaru seputar kesehatan, layanan, dan kegiatan IMI Medika.' => '了解 IMI Medika 在医疗健康、服务与活动方面的最新动态。',
        'Temukan artikel kesehatan terkini yang informatif dan bermanfaat.' => '阅读最新且实用的医疗健康文章。',
        'Search articles and news' => '搜索文章与新闻',
        'Cari artikel atau berita' => '搜索文章与新闻',
        'Filter berita' => '筛选新闻',
        'Bergabung bersama kami' => '加入我们',
        'Years of Experience' => '年经验',
        'Apply Now' => '立即申请',
        'Buka menu' => '打开菜单',
        'Navigasi utama' => '主导航',
        'Sorotan' => '精彩内容',
        'Highlights' => '精彩内容',
        'Slide sebelumnya' => '上一张',
        'Previous slide' => '上一张',
        'Slide berikutnya' => '下一张',
        'Next slide' => '下一张',
        'Jangkauan Kami' => '我们的服务网络',
        'Statistik jangkauan' => '服务网络统计',
        'Statistik perusahaan' => '公司统计',
        'Company statistics' => '公司统计',
        'Tahun Pengalaman' => '年经验',
        'Years in business' => '年经营经验',
        'Jangkauan Provinsi' => '覆盖省份',
        'Provinces covered' => '覆盖省份',
        'Visi & Misi' => '愿景与使命',
        'Vision & Mission' => '愿景与使命',
        'Visi' => '愿景',
        'Vision' => '愿景',
        'Misi' => '使命',
        'Mission' => '使命',
        'Sertifikat & Penghargaan' => '证书与奖项',
        'Certificates & Awards' => '证书与奖项',
        'Sertifikat perusahaan' => '公司证书',
        'Company certificates' => '公司证书',
        'Perbesar sertifikat' => '放大证书',
        'Enlarge certificate' => '放大证书',
        'Pilih sertifikat' => '选择证书',
        'Select certificate' => '选择证书',
        'Contact Details' => '联系方式',
        'Detail Kontak' => '联系方式',
        'Coverage statistics' => '服务网络统计',
        'Provinsi' => '个省份',
        'Provinces' => '个省份',
        'Mitra' => '家合作伙伴',
        'Partners' => '家合作伙伴',
        'Brand Kami' => '我们的品牌',
        'Our Brands' => '我们的品牌',
        'Brand mitra' => '合作品牌',
        'Pilih brand' => '选择品牌',
        'Baca selengkapnya' => '阅读更多',
        'Baca Selengkapnya' => '阅读更多',
        'Read more' => '阅读更多',
        'Kembali' => '返回',
        'Kembali ke Produk' => '返回产品列表',
        'Kembali ke Dokumentasi' => '返回资讯与活动',
        'Kembali ke Berita' => '返回新闻列表',
        'Kembali ke Karir' => '返回职业列表',
        'Kembali ke daftar' => '返回列表',
        'Tentang Kami' => '关于我们',
        'About Us' => '关于我们',
        'IMPORTIR | DISTRIBUTOR | MANUFAKTUR ALAT KESEHATAN' => '医疗器械进口商 | 分销商 | 制造商',
        'IMPORTER | DISTRIBUTOR | MEDICAL DEVICE MANUFACTURER' => '医疗器械进口商 | 分销商 | 制造商',
        'Informasi Kontak' => '联系信息',
        'Contact Information' => '联系信息',
        'Peta lokasi perusahaan' => '公司位置地图',
        'Company location map' => '公司位置地图',
        'Peta interaktif lokasi perusahaan' => '公司位置交互地图',
        'Interactive map of the company location' => '公司位置交互地图',
        'Ikuti Kami di Media Sosial' => '关注我们的社交媒体',
        'Follow Us on Social Media' => '关注我们的社交媒体',
        'Cari artikel...' => '搜索文章…',
        'Search articles...' => '搜索文章…',
        'Cari berita...' => '搜索新闻…',
        'Search news...' => '搜索新闻…',
        'Cari produk...' => '搜索产品…',
        'Search products...' => '搜索产品…',
        'Cari' => '搜索',
        'Search' => '搜索',
        'Filter' => '筛选',
        'Urutkan' => '排序',
        'Tahun' => '年份',
        'Semua Tahun' => '所有年份',
        'Terbaru' => '最新',
        'Terlama' => '最早',
        'Menampilkan' => '显示',
        'Showing' => '显示',
        'dari' => '共',
        'of' => '共',
        'Sebelumnya' => '上一页',
        '‹ Sebelumnya' => '‹ 上一页',
        '‹ Previous' => '‹ 上一页',
        'Berikutnya' => '下一页',
        'Berikutnya ›' => '下一页 ›',
        'Next ›' => '下一页 ›',
        'Belum ada produk dalam kategori ini.' => '该类别暂无产品。',
        'No products in this category yet.' => '该类别暂无产品。',
        'Gambar produk belum tersedia' => '暂无产品图片',
        'Product image unavailable' => '暂无产品图片',
        'Parameter produk' => '产品参数',
        'Beli' => '购买',
        'Buy' => '购买',
        'Brosur' => '产品手册',
        'Brochure' => '产品手册',
        'Lihat Semua Produk' => '查看所有产品',
        'View All Products' => '查看所有产品',
        'Kembali ke Produk Unggulan' => '返回精选产品',
        'Back to Featured Products' => '返回精选产品',
        'Preview Brosur' => '产品手册预览',
        'Brochure Preview' => '产品手册预览',
        'Tutup pratinjau brosur' => '关闭产品手册预览',
        'Close brochure preview' => '关闭产品手册预览',
        'Gagal Memuat Brosur' => '无法加载产品手册',
        'Unable to Load Brochure' => '无法加载产品手册',
        'File PDF tidak dapat ditampilkan.' => '无法显示 PDF 文件。',
        'The PDF file could not be displayed.' => '无法显示 PDF 文件。',
        'Buka PDF di tab baru' => '在新标签页中打开 PDF',
        'Open PDF in a new tab' => '在新标签页中打开 PDF',
        'Hubungi Kami' => '联系我们',
        'Hubungi Kami!' => '联系我们！',
        'Contact Us' => '联系我们',
        'Temukan Produk Terbaik Kami' => '发现我们的优质产品',
        'Discover Our Products' => '发现我们的优质产品',
        'Jelajahi berbagai kategori produk berkualitas tinggi untuk kebutuhan medis dan kesehatan Anda' => '探索适用于医疗与健康需求的各类优质产品。',
        'Explore our range of quality products for healthcare and medical needs' => '探索适用于医疗与健康需求的各类优质产品。',
        'Kategori Produk' => '产品类别',
        'Product Categories' => '产品类别',
        '-- Semua Kategori --' => '-- 所有类别 --',
        '-- All Categories --' => '-- 所有类别 --',
        '-- Semua Subkategori --' => '-- 所有子类别 --',
        '-- All Subcategories --' => '-- 所有子类别 --',
        'Semua Produk' => '所有产品',
        'All Products' => '所有产品',
        'View details of' => '查看详情：',
        'Lihat detail ' => '查看详情：',
        'Parameter produk' => '产品参数',
        'Preview brosur' => '预览产品手册',
        'Brosur ' => '产品手册：',
        'Brosur belum tersedia' => '暂无产品手册',
        'Perbesar brosur dengan membuka PDF lengkap di tab baru.' => '在新标签页打开完整 PDF 以查看大图。',
        'Open the full PDF in a new tab to enlarge the brochure.' => '在新标签页打开完整 PDF 以查看大图。',
        'Tampilkan subkategori ' => '显示子类别：',
        'Search articles...' => '搜索文章…',
        'DOCUMENTATION' => '资讯与活动',
        'DOKUMENTASI' => '资讯与活动',
        'Temukan artikel dan berita terkini seputar dunia kesehatan' => '了解医疗健康领域的最新文章与新闻。',
        'Discover the latest healthcare articles and news' => '了解医疗健康领域的最新文章与新闻。',
        'JELAJAHI SEKARANG' => '立即浏览',
        'EXPLORE NOW' => '立即浏览',
        'Artikel' => '文章',
        'Articles' => '文章',
        'Berita' => '新闻',
        'News' => '新闻',
        'Selengkapnya' => '阅读更多',
        'No published articles yet.' => '暂无已发布文章。',
        'Belum ada artikel yang dipublikasikan.' => '暂无已发布文章。',
        'Latest News' => '最新新闻',
        'Berita Terkini' => '最新新闻',
        'Daftar artikel' => '文章列表',
        'Daftar berita' => '新闻列表',
        'Latest news' => '最新新闻',
        'Activity gallery' => '活动相册',
        'Galeri kegiatan' => '活动相册',
        'No published news yet.' => '暂无已发布新闻。',
        'Belum ada berita yang dipublikasikan.' => '暂无已发布新闻。',
        'Baca berita selengkapnya di tab baru' => '在新标签页阅读完整新闻',
        'Lihat berita selengkapnya di tab baru' => '在新标签页阅读完整新闻',
        'Berkembang bersama kami' => '与我们共同成长',
        'Grow with us' => '与我们共同成长',
        'Bergabunglah bersama kami dan ikut menghadirkan layanan kesehatan yang lebih baik untuk Indonesia.' => '加入我们的团队，共同为印度尼西亚带来更优质的医疗服务。',
        'Join our team and help support better healthcare in Indonesia.' => '加入我们的团队，共同为印度尼西亚带来更优质的医疗服务。',
        'Lihat lowongan' => '查看职位',
        'Explore opportunities' => '查看职位',
        'Temukan peluang karir' => '探索职业机会',
        'Explore open positions' => '探索开放职位',
        'Temukan kesempatan untuk berkarya dan bertumbuh bersama tim kami.' => '寻找与我们的团队共同创造价值、共同成长的机会。',
        'Discover opportunities to make an impact with our team.' => '寻找与我们的团队共同创造价值、共同成长的机会。',
        'Terbuka' => '招聘中',
        'Open' => '招聘中',
        'Ditutup' => '截止日期',
        'Closes' => '截止日期',
        'Lihat detail' => '查看详情',
        'View details' => '查看详情',
        'Belum ada lowongan yang tersedia.' => '目前暂无职位空缺。',
        'No open positions yet' => '目前暂无职位空缺',
        'Hubungi Kami' => '联系我们',
        'Kontak Kami' => '联系我们',
        'Artikel & Berita' => '文章与新闻',
        'Cari artikel...' => '搜索文章…',
        'Cari berita...' => '搜索新闻…',
        'Cari berita' => '搜索新闻',
        'Semua' => '全部',
        'Filter berita berdasarkan tahun' => '按年份筛选新闻',
        'Urutkan berita' => '新闻排序',
        'Tahun' => '年份',
        'Urutan' => '排序',
        'Semua Tahun' => '所有年份',
        'Terbaru' => '最新',
        'Terlama' => '最早',
        'Halaman berita' => '新闻页码',
        'Halaman sebelumnya' => '上一页',
        'Halaman berikutnya' => '下一页',
        'Penulis' => '作者',
        'Nama penulis' => '作者姓名',
        'Tulis isi artikel di sini...' => '在此输入文章内容…',
        'Peta lokasi perusahaan' => '公司位置地图',
        'Company location map' => '公司位置地图',
        'Kembali ke atas' => '返回顶部',
        'Read more' => '阅读更多',
        'Selengkapnya' => '阅读更多',
        'noindex' => '不编入索引',
        'Artikel dan kegiatan terbaru' => '最新文章与活动',
        'Berita Terkini' => '最新新闻',
        'Latest News' => '最新新闻',
        'Dokumentasi' => '资讯与活动',
        'Belum ada artikel.' => '暂无文章。',
        'Belum ada berita.' => '暂无新闻。',
        'Belum ada lowongan yang tersedia.' => '目前暂无职位空缺。',
        'No jobs available right now.' => '目前暂无职位空缺。',
        'Lowongan Kerja' => '职位招聘',
        'Career Opportunities' => '职业机会',
        'Lamar Sekarang' => '立即申请',
        'Apply Now' => '立即申请',
        'Kontak Kami' => '联系我们',
        'Contact Us' => '联系我们',
        'Kami siap membantu Anda dengan segala pertanyaan dan kebutuhan mengenai produk dan layanan IMI Medika. Tim kami yang profesional siap memberikan solusi terbaik untuk Anda.' => '如您对 IMI Medika 的产品和服务有任何疑问或需求，我们随时为您提供帮助。我们的专业团队将竭诚为您提供最佳解决方案。',
        'Our team is ready to help with questions about IMI Medika products and services.' => '如您对 IMI Medika 的产品和服务有任何疑问，我们的团队随时为您提供帮助。',
        'Tutup' => '关闭',
        'Close' => '关闭',
        'Media sosial' => '社交媒体',
        'Social media' => '社交媒体',
    ];

    /** @param Closure(Request): Response $next */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (
            app()->getLocale() !== 'zh'
            || ! str_contains((string) $response->headers->get('Content-Type'), 'text/html')
            || ! is_string($response->getContent())
        ) {
            return $response;
        }

        $html = $response->getContent();
        $document = new DOMDocument('1.0', 'UTF-8');
        $previousErrorMode = libxml_use_internal_errors(true);
        $loaded = $document->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED);
        libxml_clear_errors();
        libxml_use_internal_errors($previousErrorMode);

        if (! $loaded) {
            return $response;
        }

        $xpath = new \DOMXPath($document);
        $nodes = $xpath->query('//text()[normalize-space(.) != "" and not(ancestor::script) and not(ancestor::style) and not(ancestor::noscript) and not(ancestor::code) and not(ancestor::pre) and not(ancestor::textarea) and not(ancestor::svg)] | //*/@alt | //*/@title | //*/@placeholder | //*/@aria-label | //*/@data-category-title | //*/@data-map-label | //meta[@name="description"]/@content | //meta[@property="og:title"]/@content | //meta[@property="og:description"]/@content');
        foreach ($nodes ?: [] as $node) {
            if (! $node instanceof DOMNode || $this->isInsideFooter($node)) {
                continue;
            }

            $original = $node->nodeValue ?? '';
            $trimmed = trim($original);
            $translated = self::TRANSLATIONS[$trimmed] ?? null;

            if ($translated === null && preg_match('/^(\d{1,2})\s+(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec|Okt|Agu|Des)\s+(\d{4})$/iu', $trimmed, $dateMatch)) {
                $monthNumbers = [
                    'jan' => 1, 'feb' => 2, 'mar' => 3, 'apr' => 4, 'may' => 5, 'mei' => 5,
                    'jun' => 6, 'jul' => 7, 'aug' => 8, 'agu' => 8, 'sep' => 9, 'oct' => 10,
                    'okt' => 10, 'nov' => 11, 'dec' => 12, 'des' => 12,
                ];
                $monthNumber = $monthNumbers[mb_strtolower($dateMatch[2])];
                $translated = $dateMatch[3].'年'.$monthNumber.'月'.$dateMatch[1].'日';
            }

            if ($translated === null) {
                foreach ([
                    'Berita - ' => '新闻 - ',
                    'News - ' => '新闻 - ',
                    'Artikel - ' => '文章 - ',
                    'Articles - ' => '文章 - ',
                    'Baca Selengkapnya ' => '阅读更多 ',
                    'View details of ' => '查看详情：',
                    'Lihat detail ' => '查看详情：',
                    'View details ' => '查看详情：',
                    'Beli ' => '购买 ',
                    'Buy ' => '购买 ',
                    'Preview brosur ' => '预览产品手册：',
                    'Preview brochure ' => '预览产品手册：',
                    'Brosur ' => '产品手册：',
                    'Tampilkan subkategori ' => '显示子类别：',
                    'Show subcategories ' => '显示子类别：',
                ] as $prefix => $translatedPrefix) {
                    if (str_starts_with($trimmed, $prefix)) {
                        $translated = $translatedPrefix.substr($trimmed, strlen($prefix));
                        break;
                    }
                }
            }

            if ($translated !== null && str_starts_with($translated, '产品手册：') && str_ends_with($trimmed, ' belum tersedia')) {
                $translated = mb_substr($translated, 0, -mb_strlen(' belum tersedia')).'尚未提供';
            }

            if ($translated !== null) {
                preg_match('/^\s*/u', $original, $leadingWhitespace);
                preg_match('/\s*$/u', $original, $trailingWhitespace);
                $node->nodeValue = ($leadingWhitespace[0] ?? '').$translated.($trailingWhitespace[0] ?? '');

                continue;
            }

            if (preg_match('/\p{Han}/u', $original)) {
                continue;
            }
        }

        $htmlNode = $xpath->query('//html')->item(0);
        if ($htmlNode instanceof \DOMElement) {
            $htmlNode->setAttribute('lang', 'zh-CN');
        }

        $localizedHtml = $document->saveHTML();
        $response->setContent(is_string($localizedHtml) ? preg_replace('/^<\?xml[^>]+>/', '', $localizedHtml) ?? $localizedHtml : $html);

        return $response;
    }

    private function isInsideFooter(DOMNode $node): bool
    {
        $currentNode = $node instanceof \DOMAttr ? $node->ownerElement : $node;

        while ($currentNode instanceof DOMNode) {
            if ($currentNode instanceof \DOMElement && strtolower($currentNode->tagName) === 'footer') {
                return true;
            }

            $currentNode = $currentNode->parentNode;
        }

        return false;
    }
}
