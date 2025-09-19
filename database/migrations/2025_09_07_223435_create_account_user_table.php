<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('account_user', function (Blueprint $table) {
            $table->id();
            $table->foreignUlid('account_id')->constrained('accounts')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role')->default('owner');  // owner|admin|member
            $table->boolean('is_owner')->default(false);
            $table->string('status')->default('active'); // active|invited|disabled
            $table->timestamps();
            $table->unique(['account_id','user_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('account_user');
    }
};
