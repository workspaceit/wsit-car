<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIssuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('issues', function (Blueprint $table) {
            $table->increments('id');
            $table->string('ticket_no');
            $table->string('issue_key');
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->enum('type',['General','Dealer Feed','Billing']);
            $table->integer('dealer_id')->nullable();
            $table->text('description');
            $table->string('status');
            $table->unsignedInteger('created_by');
            $table->timestamps();

            $table->index('ticket_no');
            $table->foreign('dealer_id')->references('id')->on('dealers');
            $table->foreign('created_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('issues');
    }
}
