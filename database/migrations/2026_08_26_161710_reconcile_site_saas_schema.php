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
        Schema::table('sites', function (Blueprint $table): void {
            if (! Schema::hasColumn('sites', 'owner_id')) {
                $table->foreignId('owner_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('sites', 'logo')) {
                $table->string('logo')->nullable()->after('description');
            }

            if (! Schema::hasColumn('sites', 'favicon')) {
                $table->string('favicon')->nullable()->after('logo');
            }

            if (! Schema::hasColumn('sites', 'primary_color')) {
                $table->string('primary_color')->default('#f59e0b')->after('accent');
            }

            if (! Schema::hasColumn('sites', 'secondary_color')) {
                $table->string('secondary_color')->default('#1c1917')->after('primary_color');
            }

            if (! Schema::hasColumn('sites', 'status')) {
                $table->string('status')->default('online')->after('secondary_color')->index();
            }

            if (! Schema::hasColumn('sites', 'type')) {
                $table->string('type')->default('online_store')->after('status')->index();
            }

            if (! Schema::hasColumn('sites', 'subdomain')) {
                $table->string('subdomain')->nullable()->after('type')->unique();
            }

            if (! Schema::hasColumn('sites', 'custom_domain')) {
                $table->string('custom_domain')->nullable()->after('subdomain')->unique();
            }

            if (! Schema::hasColumn('sites', 'homepage')) {
                $table->json('homepage')->nullable()->after('custom_domain');
            }

            if (! Schema::hasColumn('sites', 'social_links')) {
                $table->json('social_links')->nullable()->after('homepage');
            }

            if (! Schema::hasColumn('sites', 'contact_email')) {
                $table->string('contact_email')->nullable()->after('social_links');
            }

            if (! Schema::hasColumn('sites', 'phone')) {
                $table->string('phone')->nullable()->after('contact_email');
            }

            if (! Schema::hasColumn('sites', 'address')) {
                $table->text('address')->nullable()->after('phone');
            }
        });

        if (! Schema::hasTable('site_user')) {
            Schema::create('site_user', function (Blueprint $table): void {
                $table->foreignId('site_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('role')->default('gestor');
                $table->timestamps();
                $table->primary(['site_id', 'user_id']);
            });
        }

        if (Schema::hasTable('product_reviews') && ! Schema::hasColumn('product_reviews', 'site_id')) {
            Schema::table('product_reviews', function (Blueprint $table): void {
                $table->foreignId('site_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            });
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('product_reviews') && Schema::hasColumn('product_reviews', 'site_id')) {
            Schema::table('product_reviews', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('site_id');
            });
        }

        Schema::dropIfExists('site_user');
    }
};
