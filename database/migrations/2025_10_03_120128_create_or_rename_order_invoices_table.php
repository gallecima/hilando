<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Si existe la tabla vieja "orderinvoice", la renombramos
        if (Schema::hasTable('orderinvoice') && !Schema::hasTable('order_invoices')) {
            Schema::rename('orderinvoice', 'order_invoices');
        }

        // Si no existe la definitiva, la creamos
        if (!Schema::hasTable('order_invoices')) {
            Schema::create('order_invoices', function (Blueprint $table) {
                $table->id();

                // Relación con orders (asume tabla "orders" y PK "id")
                $table->foreignId('order_id')->constrained()->cascadeOnDelete();

                // Campos de tu modelo
                $table->string('provider', 50)->index();     // ej: 'afip', 'manual', etc.
                $table->string('title', 120)->nullable();    // etiqueta o título visible
                $table->string('number', 30)->nullable();    // nro de comprobante (string por ceros a la izq)
                $table->string('status', 20)->default('issued'); // issued|draft|error|void (ajustable)
                $table->timestamp('issued_at')->nullable();  // fecha/hora de emisión
                $table->string('file_path')->nullable();     // ruta local del PDF
                $table->string('external_url')->nullable();  // URL pública (si aplica)
                $table->json('meta')->nullable();            // payloads/respuestas extra

                $table->timestamps();

                // Índices útiles
                $table->index(['order_id', 'issued_at']);
                // Evita duplicados por proveedor+numero (permite NULL)
                $table->unique(['provider', 'number']);
            });
        } else {
            // Si ya existe, aseguramos columnas/índices mínimos (idempotente)
            Schema::table('order_invoices', function (Blueprint $table) {
                // Agregá columnas que puedan faltar sin romper si ya existen:
                if (!Schema::hasColumn('order_invoices', 'provider')) {
                    $table->string('provider', 50)->index()->after('order_id');
                }
                if (!Schema::hasColumn('order_invoices', 'title')) {
                    $table->string('title', 120)->nullable()->after('provider');
                }
                if (!Schema::hasColumn('order_invoices', 'number')) {
                    $table->string('number', 30)->nullable()->after('title');
                }
                if (!Schema::hasColumn('order_invoices', 'status')) {
                    $table->string('status', 20)->default('issued')->after('number');
                }
                if (!Schema::hasColumn('order_invoices', 'issued_at')) {
                    $table->timestamp('issued_at')->nullable()->after('status');
                }
                if (!Schema::hasColumn('order_invoices', 'file_path')) {
                    $table->string('file_path')->nullable()->after('issued_at');
                }
                if (!Schema::hasColumn('order_invoices', 'external_url')) {
                    $table->string('external_url')->nullable()->after('file_path');
                }
                if (!Schema::hasColumn('order_invoices', 'meta')) {
                    $table->json('meta')->nullable()->after('external_url');
                }
            });
        }
    }

    public function down(): void
    {
        // Si querés revertir, podés borrar la tabla (o renombrar hacia atrás si venías de 'orderinvoice')
        if (Schema::hasTable('order_invoices')) {
            Schema::drop('order_invoices');
        }
    }
};
