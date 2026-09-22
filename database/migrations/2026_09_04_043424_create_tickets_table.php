<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_no')->unique()->comment('เลขที่ใบแจ้งซ่อม');

            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->comment('ผู้แจ้งซ่อม');
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade')->comment('หมวดหมู่การซ่อม');
            
            $table->string('asset_no')->nullable()->comment('รหัสครุภัณฑ์ / อุปกรณ์');
            $table->string('location')->comment('สถานที่ / อาคาร / ห้อง');
            $table->text('symptom')->comment('รายละเอียดอาการเสีย / ปัญหา');

            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending')->comment('สถานะการแจ้งซ่อม');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
