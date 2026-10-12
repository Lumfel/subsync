<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The original reports migration inferred the foreign-key table from the
 * column name "template_id", producing a reference to a non-existent
 * "templates" table. SQLite then failed any foreign-key check (e.g. deleting
 * a resident) with "no such table: main.templates". Rebuild the table so the
 * key points at "report_templates".
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            return;
        }

        $row = DB::selectOne("select sql from sqlite_master where type = 'table' and name = 'reports'");
        // Fresh installs already have the corrected key (and no '"templates"' reference).
        if (! $row || strpos($row->sql, '"templates"') === false) {
            return;
        }

        Schema::disableForeignKeyConstraints();
        Schema::rename('reports', 'reports_old');
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resident_id')->constrained('residents')->cascadeOnDelete();
            $table->foreignId('template_id')->constrained('report_templates')->cascadeOnDelete();
            $table->string('subject');
            $table->text('content');
            $table->string('status')->default('Generated');
            $table->timestamps();
        });
        DB::statement('insert into reports (id, resident_id, template_id, subject, content, status, created_at, updated_at)
            select id, resident_id, template_id, subject, content, status, created_at, updated_at from reports_old');
        Schema::drop('reports_old');
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // Intentionally empty — reverting would reintroduce the broken foreign key.
    }
};
