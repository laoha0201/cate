<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cate', function (Blueprint $table) {
            $table->increments('id');
            $table->string('groups', 255)->nullable();
            $table->unsignedInteger('parent_id')->default(0);
            $table->string('parents', 255)->nullable();
            $table->string('name', 30);
            $table->string('slug', 30)->nullable();
            $table->string('base', 50)->index('base')->comment('关联标记');
            $table->smallInteger('order')->default(0)->comment('栏目排序');
            $table->string('thumb')->nullable();
            $table->tinyInteger('allow_publish')->default(1)->comment('允许发表');
            $table->tinyInteger('allow_comment')->default(0)->comment('允许评论');
            $table->text('ext')->nullable()->comment('扩展设置');
            $table->string('desc', 255)->nullable()->comment('介绍');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['parent_id', 'order', 'id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cate');
    }
}
