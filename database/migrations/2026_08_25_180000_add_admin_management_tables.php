<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->string('role')->default('vendedor')->after('email');
            });
        }

        if (! Schema::hasColumn('categories', 'name')) {
            Schema::table('categories', function (Blueprint $table): void {
                $table->string('name')->index()->after('id');
            });
        }

        if (! Schema::hasColumn('categories', 'slug')) {
            Schema::table('categories', function (Blueprint $table): void {
                $table->string('slug')->index()->after('name');
            });
        }

        Schema::table('products', function (Blueprint $table): void {
            if (! Schema::hasColumn('products', 'category_id')) {
                $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('products', 'name')) {
                $table->string('name');
            }
            if (! Schema::hasColumn('products', 'slug')) {
                $table->string('slug')->index();
            }
            if (! Schema::hasColumn('products', 'description')) {
                $table->text('description')->nullable();
            }
            if (! Schema::hasColumn('products', 'price')) {
                $table->decimal('price', 10, 2)->default(0);
            }
            if (! Schema::hasColumn('products', 'image_url')) {
                $table->string('image_url')->nullable();
            }
            if (! Schema::hasColumn('products', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
            if (! Schema::hasColumn('products', 'stock')) {
                $table->unsignedInteger('stock')->default(0);
            }
            if (! Schema::hasColumn('products', 'minimum_stock')) {
                $table->unsignedInteger('minimum_stock')->default(0);
            }
            if (! Schema::hasColumn('products', 'reserved_stock')) {
                $table->unsignedInteger('reserved_stock')->default(0);
            }
        });

        Schema::create('customers', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable()->index();
            $table->string('phone')->nullable();
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('seller_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('sold_at');
            $table->decimal('total', 10, 2)->default(0);
            $table->string('payment_method')->default('pendente');
            $table->string('status')->default('pendente');
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name');
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total', 10, 2);
            $table->timestamps();
        });

        Schema::create('stock_movements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type');
            $table->integer('quantity');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('customers');
        Schema::table('products', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('category_id');
            $table->dropColumn(['name', 'slug', 'description', 'price', 'stock', 'minimum_stock', 'reserved_stock', 'image_url', 'is_active']);
        });
        Schema::table('categories', function (Blueprint $table): void {
            $table->dropColumn(['name', 'slug']);
        });
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('role');
        });
    }
};
