<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Clients have one logo instead of several images. A client without a logo keeps its first
     * image as the logo.
     */
    public function up(): void
    {
        DB::table('clients')->whereNull('logo')->whereNotNull('images')->orderBy('id')
            ->each(function (object $client) {
                $first = json_decode((string) $client->images, true)[0] ?? null;

                if ($first) {
                    DB::table('clients')->where('id', $client->id)->update(['logo' => $first]);
                }
            });

        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('images');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->json('images')->nullable()->after('description');
        });
    }
};
