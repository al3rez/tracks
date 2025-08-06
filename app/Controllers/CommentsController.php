<?php

namespace App\Controllers;

use Tracks\ActionController\Base;
use App\Models\Comment;
use App\Models\Post;

class CommentsController extends Base
{
    public function create(): void
    {
        $postId = $this->param('post_id');
        $post = Post::find($postId);
        
        if (!$post) {
            $this->redirectTo('/posts', ['alert' => 'Post not found']);
            return;
        }
        
        $comment = new Comment($this->commentParams());
        $comment->post_id = $postId;
        
        if ($comment->save()) {
            $this->redirectTo("/posts/{$postId}", ['notice' => 'Comment added successfully!']);
        } else {
            $this->redirectTo("/posts/{$postId}", ['alert' => 'Could not add comment. Please try again.']);
        }
    }
    
    private function commentParams(): array
    {
        return $this->requireParams('comment', ['author', 'content']);
    }
}