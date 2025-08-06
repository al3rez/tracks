<?php

namespace App\Models;

use Tracks\ActiveRecord\Base;

class Comment extends Base
{
    protected static array $validations = [
        'author' => ['presence' => true],
        'content' => ['presence' => true],
        'post_id' => ['presence' => true],
    ];
    
    protected static array $callbacks = [
        'beforeCreate' => ['setCreatedAt'],
    ];
    
    protected function setCreatedAt(): void
    {
        if (!isset($this->created_at)) {
            $this->created_at = date('Y-m-d H:i:s');
        }
    }
    
    public function post(): ?Post
    {
        return Post::find($this->post_id);
    }
}