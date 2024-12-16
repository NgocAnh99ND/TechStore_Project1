<?php

use App\Models\Catalogue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Catalogue::class)->constrained();
            $table->string("name", 255);
            $table->string("slug", 255)->unique();
            $table->string("sku", 255)->unique();
            $table->string("img_thumbnail", 255)->nullable();
            $table->double("price_regular");
            $table->double("price_sale")->nullable();
            $table->string("short_description", 255)->nullable();
            $table->string("description", 255)->nullable();
            $table->string("screen_size", 50)->nullable();
            $table->string("battery_capacity", 50)->nullable();
            $table->string("camera_resolution", 100)->nullable();
            $table->string("operating_system", 50)->nullable();
            $table->string("processor", 100)->nullable();
            $table->string("ram", 50)->nullable();
            $table->string("storage", 50)->nullable();
            $table->string("sim_type", 50)->nullable();
            $table->string("network_connectivity", 100);
            $table->boolean("is_active")->default(1);
            $table->boolean("is_hot_deal")->default(value: 1);
            $table->boolean("is_good_deal")->default(1);
            $table->boolean("is_new")->default(1);
            $table->boolean("is_show_home")->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
