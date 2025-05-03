<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    //run
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('google2fa_secret')->nullable()->change();
        });
    }
    // reverse
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('google2fa_secret')->nullable()->change();
        });
    }
    
};
