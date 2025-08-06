<?php

use Tracks\ActiveRecord\Migration;

class CreatePosts extends Migration
{
    public function up(): void
    {
        $this->createTable('posts', function($t) {
            $t->string('title');
            $t->text('content');
            $t->string('author');
            $t->string('slug');
            $t->boolean('published', false);
            $t->timestamps();
            $t->index('slug');
            $t->index('published');
        });
    }
    
    public function down(): void
    {
        $this->dropTable('posts');
    }
}