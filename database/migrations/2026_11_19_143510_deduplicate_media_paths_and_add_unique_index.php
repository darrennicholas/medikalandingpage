<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::transaction(function (): void {
            $duplicates = DB::table('media')
                ->select('path')
                ->groupBy('path')
                ->havingRaw('COUNT(*) > 1')
                ->pluck('path');
            $references = [
                'hero_slides' => ['media_id'],
                'content_blocks' => ['media_id'],
                'brands' => ['logo_media_id'],
                'certificates' => ['media_id'],
                'products' => ['media_id', 'logo_top_media_id', 'logo_bottom_media_id'],
                'documentations' => ['cover_media_id'],
                'seo_metas' => ['og_media_id'],
            ];

            foreach ($duplicates as $path) {
                $media = DB::table('media')->where('path', $path)
                    ->orderByRaw('deleted_at IS NULL DESC')
                    ->orderBy('id')
                    ->get();
                $canonical = $media->first();
                $duplicateIds = $media->skip(1)->pluck('id')->all();

                foreach (['alt_text_id', 'alt_text_en', 'alt_text_zh'] as $column) {
                    if (blank($canonical->{$column})) {
                        $canonical->{$column} = $media->first(fn (object $item): bool => filled($item->{$column}))?->{$column};
                    }
                }

                DB::table('media')->where('id', $canonical->id)->update([
                    'alt_text_id' => $canonical->alt_text_id,
                    'alt_text_en' => $canonical->alt_text_en,
                    'alt_text_zh' => $canonical->alt_text_zh,
                ]);

                foreach ($duplicateIds as $duplicateId) {
                    foreach ($references as $table => $columns) {
                        foreach ($columns as $column) {
                            DB::table($table)->where($column, $duplicateId)->update([$column => $canonical->id]);
                        }
                    }

                    $duplicateGalleryRows = DB::table('documentation_media')->where('media_id', $duplicateId)->get();
                    foreach ($duplicateGalleryRows as $gallery) {
                        $existing = DB::table('documentation_media')
                            ->where('documentation_id', $gallery->documentation_id)
                            ->where('media_id', $canonical->id)
                            ->first();

                        if ($existing) {
                            DB::table('documentation_media')->where('id', $existing->id)->update([
                                'caption_id' => $existing->caption_id ?: $gallery->caption_id,
                                'caption_en' => $existing->caption_en ?: $gallery->caption_en,
                                'caption_zh' => $existing->caption_zh ?: $gallery->caption_zh,
                            ]);
                            DB::table('documentation_media')->where('id', $gallery->id)->delete();
                        } else {
                            DB::table('documentation_media')->where('id', $gallery->id)->update(['media_id' => $canonical->id]);
                        }
                    }

                    foreach (['hero_slides', 'content_blocks'] as $table) {
                        foreach (DB::table($table)->whereNotNull('draft_payload')->get(['id', 'draft_payload']) as $record) {
                            $payload = json_decode($record->draft_payload, true);
                            if ((int) ($payload['attributes']['media_id'] ?? 0) !== (int) $duplicateId) {
                                continue;
                            }

                            $payload['attributes']['media_id'] = $canonical->id;
                            DB::table($table)->where('id', $record->id)->update(['draft_payload' => json_encode($payload)]);
                        }
                    }
                }

                DB::table('media')->whereIn('id', $duplicateIds)->delete();
            }
        });

        Schema::table('media', function (Blueprint $table): void {
            $table->unique('path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('media', function (Blueprint $table): void {
            $table->dropUnique('media_path_unique');
        });
    }
};
