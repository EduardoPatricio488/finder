<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->decimal('subtotal', 10, 2)->default(0)->after('sold_at');
            $table->decimal('discount', 10, 2)->default(0)->after('subtotal');
            $table->decimal('tax', 10, 2)->default(0)->after('discount');
            $table->decimal('shipping', 10, 2)->default(0)->after('tax');
        });

        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('method');
            $table->string('status')->default('pendente');
            $table->timestamp('paid_at')->nullable();
            $table->string('transaction_reference')->nullable();
            $table->timestamps();
        });

        Schema::create('goals', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->decimal('target', 10, 2);
            $table->date('starts_at');
            $table->date('ends_at');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('coupons', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->index();
            $table->string('type');
            $table->decimal('value', 10, 2);
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->unsignedInteger('maximum_uses')->nullable();
            $table->unsignedInteger('uses')->default(0);
            $table->decimal('minimum_order_value', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('suppliers', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('company')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('tax_number')->nullable();
            $table->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->text('description');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('goals');
        Schema::dropIfExists('payments');
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn(['subtotal', 'discount', 'tax', 'shipping']);
        });
    }
};
