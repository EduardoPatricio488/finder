<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_submissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->string('form_type', 40)->index();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->text('message')->nullable();
            $table->json('payload')->nullable();
            $table->string('status', 30)->default('new')->index();
            $table->timestamps();

            $table->index(['site_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_submissions');
    }
};
