<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ([
            'logo_atas_6a8bcc27e4502_1787546663.png' => 'logo_atas.png',
            'logo_bawah_6a9126db3eea7_1787897563.png' => 'logo_watermark.png',
        ] as $oldName => $newName) {
            DB::table('media')->where('path', 'images/products/logos/'.$oldName)->update([
                'path' => 'images/products/logos/'.$newName,
                'file_name' => $newName,
                'original_name' => $newName,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // The canonical asset filenames remain valid if this migration is rolled back.
    }
};
