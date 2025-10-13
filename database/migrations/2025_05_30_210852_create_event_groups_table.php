<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('event_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->string('group');
            $table->timestamps();
            
            // Уникальный индекс для предотвращения дублирования
            $table->unique(['event_id', 'group']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('event_groups');
    }
};
