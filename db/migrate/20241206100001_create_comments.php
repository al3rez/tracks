<?php

use Tracks\ActiveRecord\Migration;

class CreateComments extends Migration
{
    public function up(): void
    {
        $this->createTable('comments', function($t) {
            $t->references('post');
            $t->string('author');
            $t->text('content');
            $t->timestamps();
        });
    }
    
    public function down(): void
    {
        $this->dropTable('comments');
    }
}