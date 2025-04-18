<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecouvrementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recouvrements', function (Blueprint $table) {
            $table->id();
            $table->foreignId("client_id")
                ->nullable()
                ->constrained("clients", "id")
                ->onUpdate("CASCADE")
                ->onDelete("CASCADE");
            $table->text("comments")->nullable();
            $table->boolean("verified")->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recouvrements');
    }
}
