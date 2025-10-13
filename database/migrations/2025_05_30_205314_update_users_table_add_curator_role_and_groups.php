<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Сначала изменим тип поля role на строку, чтобы добавить новое значение
        Schema::table('users', function (Blueprint $table) {
            // Создаем временное поле
            $table->string('role_temp')->default('student');
        });

        // Копируем данные из старого поля в новое
        DB::table('users')->get()->each(function ($user) {
            DB::table('users')
                ->where('id', $user->id)
                ->update(['role_temp' => $user->role]);
        });

        // Удаляем старое поле и переименовываем новое
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('role_temp', 'role');
        });

        // Добавляем поле для хранения групп куратора
        Schema::table('users', function (Blueprint $table) {
            $table->json('curator_groups')->nullable();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('curator_groups');
            
            // Создаем временное поле enum
            $table->enum('role_temp', ['student', 'admin'])->default('student');
        });

        // Копируем данные обратно, преобразуя 'curator' в 'admin'
        DB::table('users')->get()->each(function ($user) {
            $role = $user->role === 'curator' ? 'admin' : $user->role;
            DB::table('users')
                ->where('id', $user->id)
                ->update(['role_temp' => $role]);
        });

        // Удаляем старое поле и переименовываем новое
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('role_temp', 'role');
        });
    }
};
