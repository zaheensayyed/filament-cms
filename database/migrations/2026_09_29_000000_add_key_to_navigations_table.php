<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('navigations', function (Blueprint $table) {
            $table->string('key')->nullable()->unique()->after('id');
        });

        // Backfill a stable key from the display name so existing menus can be
        // looked up by key straight away (e.g. "Main Menu" → "main-menu").
        $taken = [];

        foreach (DB::table('navigations')->orderBy('id')->get(['id', 'name']) as $navigation) {
            $key = Str::slug($navigation->name) ?: 'menu';

            if (in_array($key, $taken, true)) {
                $key .= '-' . $navigation->id;
            }

            $taken[] = $key;

            DB::table('navigations')->where('id', $navigation->id)->update(['key' => $key]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('navigations', function (Blueprint $table) {
            $table->dropUnique(['key']);
            $table->dropColumn('key');
        });
    }
};
