<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assigned_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_to_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('machine_id')->nullable()->constrained('machines')->nullOnDelete();
            $table->string('title');
            $table->string('task_type')->default('overtime'); // overtime, maintenance, supervision, operational, general
            $table->string('priority')->default('high'); // urgent, high, normal, low
            $table->string('status')->default('in_progress'); // pending, in_progress, completed, paused
            $table->text('description')->nullable();
            $table->date('due_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('task_follow_ups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_assignment_id')->constrained('task_assignments')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('report_date');
            $table->text('notes');
            $table->integer('downtime_minutes')->default(0);
            $table->string('spare_parts_used')->nullable();
            $table->string('machine_status')->default('running'); // running, stopped, maintenance
            $table->integer('progress_percentage')->default(100);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_follow_ups');
        Schema::dropIfExists('task_assignments');
    }
};
