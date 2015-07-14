<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		//
		Schema::create('users',function($table){
			$table->increments('user_id');
			$table->string('user_name',50)->unique();
			$table->string('password',255);
			$table->string('email',255)->unique();
			$table->string('firstname',20);
			$table->string('lastname',20);
			$table->boolean('activated')->default(0);
			$table->integer('rid')->default(1);
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
		//
		Schema::drop('users');
	}

}
