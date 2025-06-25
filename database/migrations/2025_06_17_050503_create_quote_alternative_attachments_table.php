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
        Schema::create('quote_alternative_attachments', function (Blueprint $table) {
            $table->id();
            // Clave foránea que vincula el adjunto a una alternativa de cotización
            $table->foreignId('quote_alternative_id')
                  ->constrained('quote_alternatives') // Asegúrate de que esta tabla ya exista
                  ->onDelete('cascade'); // Si se elimina la alternativa, se eliminan sus adjuntos

            $table->string('file_name'); // Nombre original del archivo (ej. "condiciones.pdf")
            $table->string('file_path'); // Ruta interna donde se guarda el archivo (ej. "public/attachments/pdf_123.pdf")
            $table->string('file_url');  // URL pública para acceder al archivo (ej. "/storage/attachments/pdf_123.pdf")
            $table->string('mime_type'); // Tipo MIME del archivo (ej. "application/pdf")
            $table->unsignedBigInteger('file_size'); // Tamaño del archivo en bytes

            $table->timestamps(); // created_at, updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote_alternative_attachments');
    }
};
