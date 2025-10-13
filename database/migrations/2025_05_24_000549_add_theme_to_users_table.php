<?php

     use Illuminate\Database\Migrations\Migration;
     use Illuminate\Database\Schema\Blueprint;
     use Illuminate\Support\Facades\Schema;

     class AddThemeToUsersTable extends Migration
     {
         public function up()
         {
             Schema::table('users', function (Blueprint $table) {
                 $table->string('theme')->default('light')->after('role');
             });
         }

         public function down()
         {
             Schema::table('users', function (Blueprint $table) {
                 $table->dropColumn('theme');
             });
         }
     }