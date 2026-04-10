<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('slug')->unique()->after('name');
            $table->text('bio')->nullable()->after('email');
            $table->string('profile_image')->nullable()->after('bio');
            $table->string('website')->nullable()->after('profile_image');
            $table->string('location')->nullable()->after('website');
            $table->string('facebook')->nullable()->after('location');
            $table->string('twitter')->nullable()->after('facebook');
            $table->string('ghost_id')->nullable()->index()->after('twitter');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['slug', 'bio', 'profile_image', 'website', 'location', 'facebook', 'twitter', 'ghost_id']);
        });
    }
};
