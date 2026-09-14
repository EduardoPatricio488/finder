<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sites', function (Blueprint $table): void {
            if (! Schema::hasColumn('sites', 'theme')) {
                $table->json('theme')->nullable()->after('social_links');
            }
            if (! Schema::hasColumn('sites', 'settings')) {
                $table->json('settings')->nullable()->after('theme');
            }
            if (! Schema::hasColumn('sites', 'seo')) {
                $table->json('seo')->nullable()->after('settings');
            }
            if (! Schema::hasColumn('sites', 'published_at')) {
                $table->timestamp('published_at')->nullable()->after('is_published')->index();
            }
        });

        Schema::create('site_pages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('status')->default('draft')->index();
            $table->boolean('is_homepage')->default(false)->index();
            $table->json('seo')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['site_id', 'slug']);
            $table->index(['site_id', 'status']);
        });

        Schema::create('site_sections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_page_id')->constrained()->cascadeOnDelete();
            $table->string('type')->index();
            $table->string('label')->nullable();
            $table->json('content')->nullable();
            $table->json('settings')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_visible')->default(true)->index();
            $table->timestamps();
            $table->index(['site_page_id', 'sort_order']);
        });

        Schema::create('site_menus', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('location')->default('header')->index();
            $table->timestamps();
            $table->unique(['site_id', 'name']);
        });

        Schema::create('site_menu_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_menu_id')->constrained()->cascadeOnDelete();
            $table->foreignId('site_page_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('site_menu_items')->nullOnDelete();
            $table->string('label');
            $table->string('url')->nullable();
            $table->string('target')->default('_self');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_visible')->default(true)->index();
            $table->timestamps();
            $table->index(['site_menu_id', 'sort_order']);
        });

        Schema::create('site_media', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('disk')->default('public');
            $table->string('path');
            $table->string('original_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->string('alt_text')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->timestamps();
            $table->index(['site_id', 'created_at']);
        });

        Schema::create('site_analytics_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->foreignId('page_id')->nullable()->constrained('site_pages')->nullOnDelete();
            $table->string('event_type')->default('page_view')->index();
            $table->string('path')->nullable();
            $table->string('referrer')->nullable();
            $table->string('device_type')->nullable();
            $table->string('country_code', 2)->nullable();
            $table->string('session_hash', 64)->nullable()->index();
            $table->timestamp('occurred_at')->index();
            $table->timestamps();
            $table->index(['site_id', 'occurred_at']);
        });

        Schema::create('site_admin_audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['site_id', 'created_at']);
            $table->index(['actor_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_admin_audit_logs');
        Schema::dropIfExists('site_analytics_events');
        Schema::dropIfExists('site_media');
        Schema::dropIfExists('site_menu_items');
        Schema::dropIfExists('site_menus');
        Schema::dropIfExists('site_sections');
        Schema::dropIfExists('site_pages');

        Schema::table('sites', function (Blueprint $table): void {
            foreach (['theme', 'settings', 'seo', 'published_at'] as $column) {
                if (Schema::hasColumn('sites', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
