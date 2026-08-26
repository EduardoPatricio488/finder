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

        $casaCoId = DB::table('sites')->where('slug', 'casa-co')->value('id');

        if ($casaCoId === null) {
            return;
        }

        DB::table('sites')->where('id', $casaCoId)->update([
            'type' => 'online_store',
            'status' => 'online',
            'subdomain' => 'casa-co',
            'primary_color' => '#f59e0b',
            'secondary_color' => '#1c1917',
            'homepage' => json_encode([
                'hero_title' => 'Coisas bonitas para viver melhor.',
                'hero_subtitle' => 'Uma seleção cuidada de artigos para a casa, para oferecer e para aproveitar os pequenos momentos do dia.',
                'primary_button_label' => 'Explorar produtos',
                'sections' => [
                    'hero' => true,
                    'featured_products' => true,
                    'about' => true,
                    'categories' => true,
                    'newsletter' => true,
                    'footer' => true,
                ],
            ]),
        ]);

        foreach (['products', 'orders', 'categories', 'customers', 'payments', 'stock_movements', 'store_configs', 'promotions', 'product_reviews'] as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'site_id')) {
                DB::table($tableName)->whereNull('site_id')->update(['site_id' => $casaCoId]);
            }
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
