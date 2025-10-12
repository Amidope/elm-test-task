<?php

use App\Models\Account;
use App\Models\ApiService;
use App\Models\TokenType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApiTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('api_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Account::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(ApiService::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(TokenType::class)->constrained()->cascadeOnDelete();
            $table->text('token');
            $table->text('login')->nullable();
            $table->text('password')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['account_id', 'api_service_id', 'token_type_id'], 'unique_account_service_token_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('api_tokens');
    }
}
