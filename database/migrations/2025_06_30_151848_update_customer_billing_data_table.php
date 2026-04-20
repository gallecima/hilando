<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_billing_data', function (Blueprint $table) {
            $table->foreignId('customer_id')->after('id')->constrained('customers')->onDelete('cascade');
            $table->string('business_name')->after('customer_id');
            $table->string('document_number')->after('business_name');
            $table->enum('tax_status', ['Responsable Inscripto', 'Monotributista', 'Consumidor Final', 'Exento'])->after('document_number');
            $table->string('address_line')->after('tax_status');
            $table->string('city')->after('address_line');
            $table->string('province')->after('city');
            $table->string('postal_code')->after('province');
            $table->string('country')->after('postal_code');
            $table->boolean('is_default')->default(0)->after('country');
        });
    }

    public function down(): void
    {
        Schema::table('customer_billing_data', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropColumn([
                'customer_id', 'business_name', 'document_number', 'tax_status',
                'address_line', 'city', 'province', 'postal_code', 'country', 'is_default'
            ]);
        });
    }
};
