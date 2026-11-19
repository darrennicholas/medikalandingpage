<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MandarinExistingContentSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $this->insertTranslations('hero_slide_translations', 'hero_slide_id', [
                1 => ['title' => '印度尼西亚英特斯卡拉医疗', 'subtitle' => '携手开创医疗未来', 'cta_label' => '联系我们'],
                2 => ['title' => '测试', 'subtitle' => 'W', 'cta_label' => 'CDC'],
            ]);
            $this->insertTranslations('content_block_translations', 'content_block_id', [
                1 => ['title' => '我们的服务网络', 'subtitle' => '销售网络', 'body' => '印度尼西亚英特斯卡拉医疗有限公司的分销网络覆盖印度尼西亚全境，从沙璜到马老奇。我们致力于将高品质医疗器械送达全国各地。', 'metrics' => '[]', 'buttons' => null],
                2 => ['title' => '随时为您提供医疗器械支持', 'subtitle' => '如需了解更多信息，欢迎随时联系我们。', 'body' => null, 'cta_label' => '联系我们！', 'buttons' => json_encode([['url' => '/zh/kontak', 'icon' => null, 'label' => '联系我们！', 'style' => 'primary']], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)],
                3 => ['title' => '关于我们', 'subtitle' => '医疗器械进口商 | 分销商 | 制造商', 'body' => '<p>印度尼西亚英特斯卡拉医疗有限公司是一家医疗器械进口、分销与制造企业，专注于体外诊断（IVD）产品。凭借CE和ISO认证，我们致力于提供安全、优质且符合国际标准的产品，助力提升医疗服务水平。</p><p>我们持续在技术、效率和可及性方面创新，并依托丰富的行业经验、广泛的分销网络和专业团队，为印度尼西亚各地的医疗机构、实验室及医疗服务提供方提供战略合作支持。</p>'],
                4 => ['title' => '愿景', 'body' => '成为领先的战略合作伙伴，为印度尼西亚更加可靠、可持续的医疗未来提供符合全球标准的医疗器械解决方案。'],
                5 => ['title' => '印度尼西亚英特斯卡拉医疗', 'body' => '医疗器械进口商、分销商与制造商，为印度尼西亚提供优质诊断解决方案。'],
                6 => ['title' => '加入英特斯卡拉医疗', 'body' => '加入我们的团队，共同助力印度尼西亚医疗服务的发展。'],
            ]);
            $this->insertTranslations('mission_item_translations', 'mission_item_id', [
                1 => ['text' => '提供符合国际标准的高品质医疗器械，助力实现精准医疗服务。'],
                2 => ['text' => '开发创新且具有竞争力的诊断解决方案，有效满足医疗机构的需求。'],
                3 => ['text' => '以诚信、专业精神和持续服务，与客户建立长期合作关系。'],
            ]);
            $this->insertTranslations('certificate_translations', 'certificate_id', [
                1 => ['title' => '医疗器械良好分销规范', 'description' => '印度尼西亚卫生部'],
                2 => ['title' => 'ISO 9001:2015', 'description' => '已获认证'],
                3 => ['title' => '医疗器械良好分销规范（CDAKB）', 'description' => '卫生部'],
                4 => ['title' => 'ISO 9001:2015 TÜV Nord', 'description' => 'TÜV NORD'],
                5 => ['title' => '国内成分水平', 'description' => '印度尼西亚工业部'],
                6 => ['title' => '印度尼西亚卫生部 CDAKB 认证', 'description' => '印度尼西亚卫生部'],
            ]);
            $this->insertTranslations('product_category_translations', 'product_category_id', [
                1 => ['name' => '药物滥用检测'],
                2 => ['name' => '快速检测'],
                3 => ['name' => '分子诊断'],
                4 => ['name' => '快速检测（主分类）'],
                5 => ['name' => '即时检测（POCT）'],
                6 => ['name' => '医疗仪器'],
                7 => ['name' => '医疗耗材'],
            ], true);
            $this->insertTranslations('product_translations', 'product_id', [
                1 => ['name' => 'DOA 10项毒品联合检测试剂盒', 'description' => '毒品滥用筛查 | 10项联合检测'],
                2 => ['name' => 'DOA 3项毒品联合检测试剂盒', 'description' => '毒品滥用筛查 | AMP、THC、METH'],
                3 => ['name' => 'DOA 3项毒品联合检测试剂盒', 'description' => '毒品滥用筛查 | AMP、MOP、METH'],
                4 => ['name' => 'DOA 3项毒品联合检测试剂盒', 'description' => '毒品滥用筛查 | AMP、THC、MOP'],
                5 => ['name' => 'DOA 5项毒品联合检测试剂盒', 'description' => '毒品滥用筛查 | 5项联合检测'],
                6 => ['name' => 'DOA 6项毒品联合检测试剂盒', 'description' => '毒品滥用筛查 | 6项联合检测'],
                7 => ['name' => 'DOA 7项毒品联合检测试剂盒', 'description' => '毒品滥用筛查 | 7项联合检测'],
                8 => ['name' => 'DOA 安非他明单项检测试剂盒', 'description' => '毒品滥用筛查 | 安非他明（AMP）单项检测'],
                9 => ['name' => 'DOA 苯二氮䓬类单项检测试剂盒', 'description' => '毒品滥用筛查 | 苯二氮䓬类（BZO）单项检测'],
                10 => ['name' => 'DOA MDMA 单项检测试剂盒', 'description' => '毒品滥用筛查 | MDMA 单项检测'],
                11 => ['name' => 'DOA 甲基苯丙胺单项检测试剂盒', 'description' => '毒品滥用筛查 | 甲基苯丙胺（METH）单项检测'],
                12 => ['name' => 'DOA 吗啡单项检测试剂盒', 'description' => '毒品滥用筛查 | 吗啡（MOP）单项检测'],
                13 => ['name' => 'DOA 四氢大麻酚单项检测试剂盒', 'description' => '毒品滥用筛查 | 四氢大麻酚（THC）单项检测'],
                14 => ['name' => '登革热 IgG/IgM 快速检测试剂盒', 'description' => '快速检测 | 感染性疾病'],
                15 => ['name' => 'Y', 'description' => 'Y'],
                16 => ['name' => '新款基因组检测试剂盒', 'description' => '<p><strong>frfrfr</strong></p>'],
            ], true);
            $this->insertTranslations('documentation_translations', 'documentation_id', [
                1 => ['title' => '英特斯卡拉医疗亮相 2026 年医院博览会', 'excerpt' => '展示医疗器械解决方案，拓展行业联系，并进一步践行成为医疗服务可信赖合作伙伴的承诺。', 'body' => '英特斯卡拉医疗与 Interskala Sehat Sejahtera 于 2026 年 10 月 7 日至 10 日参加了在丹格朗 ICE BSD City 举办的 2026 年印度尼西亚国际医院博览会。借此展会，我们展示了优质产品和医疗器械解决方案，并与经销商、医疗专业人士及行业从业者交流。'],
                2 => ['title' => '测试内容', 'excerpt' => '测试摘要', 'body' => '测试正文'],
            ], true);
            $this->insertTranslations('contact_info_translations', 'contact_info_id', [
                1 => ['address' => '印度尼西亚，雅加达特别首都地区，西雅加达，卡利德雷斯，Daan Mogot 路 KM 18，Green Sedayu Biz Park DM 9 座 62 号，邮编 11840。', 'office_hours' => '星期一至星期五，08:00–17:00（西印度尼西亚时间）'],
            ]);

            $seo = [
                'home' => ['meta_title' => '印度尼西亚英特斯卡拉医疗', 'meta_description' => '印度尼西亚英特斯卡拉医疗为印度尼西亚各地提供优质医疗器械。'],
                'about' => ['meta_title' => '关于我们 - 印度尼西亚英特斯卡拉医疗', 'meta_description' => '了解印度尼西亚英特斯卡拉医疗的愿景、使命、经验与认证。'],
                'products' => ['meta_title' => '我们的产品 - 印度尼西亚英特斯卡拉医疗', 'meta_description' => '探索印度尼西亚英特斯卡拉医疗的医疗器械与诊断产品。'],
                'documentation' => ['meta_title' => '资讯与活动 - 印度尼西亚英特斯卡拉医疗', 'meta_description' => '阅读印度尼西亚英特斯卡拉医疗的最新文章与活动。'],
                'careers' => ['meta_title' => '职业发展 - 印度尼西亚英特斯卡拉医疗', 'meta_description' => '探索印度尼西亚英特斯卡拉医疗的职业机会。'],
                'contact' => ['meta_title' => '联系我们 - 印度尼西亚英特斯卡拉医疗', 'meta_description' => '联系印度尼西亚英特斯卡拉医疗，了解产品与服务信息。'],
            ];
            foreach ($seo as $pageKey => $translation) {
                DB::table('seo_metas')->insertOrIgnore([
                    'page_key' => $pageKey,
                    'lang' => 'zh',
                    ...$translation,
                    'robots_index' => 1,
                ]);
            }

            DB::table('media')->where('id', 2)->whereNull('alt_text_zh')->update(['alt_text_zh' => '印度尼西亚医疗器械服务网络覆盖地图']);
            $this->preserveCompanyName();
        });
    }

    private function preserveCompanyName(): void
    {
        $translationScopes = [
            ['hero_slide_translations', 'hero_slide_id', [1], ['title']],
            ['content_block_translations', 'content_block_id', [1, 3, 6], ['title', 'subtitle', 'body']],
            ['seo_metas', 'page_key', ['home', 'about', 'products', 'documentation', 'careers', 'contact'], ['meta_title', 'meta_description']],
        ];
        $companyNameReplacements = [
            '印度尼西亚英特斯卡拉医疗有限公司' => 'PT Interskala Medika Indonesia',
            '印度尼西亚英特斯卡拉医疗' => 'PT Interskala Medika Indonesia',
            '英特斯卡拉医疗' => 'PT Interskala Medika Indonesia',
        ];

        foreach ($translationScopes as [$table, $foreignKey, $recordIds, $fields]) {
            $rows = DB::table($table)
                ->where('lang', 'zh')
                ->whereIn($foreignKey, $recordIds)
                ->get(['id', ...$fields]);

            foreach ($rows as $row) {
                $updates = [];

                foreach ($fields as $field) {
                    $currentValue = $row->{$field};
                    if (! is_string($currentValue)) {
                        continue;
                    }

                    $updatedValue = str_replace(array_keys($companyNameReplacements), array_values($companyNameReplacements), $currentValue);
                    if ($updatedValue !== $currentValue) {
                        $updates[$field] = $updatedValue;
                    }
                }

                if ($updates !== []) {
                    DB::table($table)->where('id', $row->id)->update($updates);
                }
            }
        }
    }

    /** @param array<int, array<string, string|null>> $translations */
    private function insertTranslations(string $table, string $foreignKey, array $translations, bool $copyEnglishSlug = false): void
    {
        foreach ($translations as $recordId => $attributes) {
            if ($copyEnglishSlug && ! array_key_exists('slug', $attributes)) {
                $attributes['slug'] = DB::table($table)->where($foreignKey, $recordId)->where('lang', 'en')->value('slug');
            }

            DB::table($table)->insertOrIgnore([
                $foreignKey => $recordId,
                'lang' => 'zh',
                ...$attributes,
            ]);
        }
    }
}
