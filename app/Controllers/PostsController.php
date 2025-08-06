<?php

namespace App\Controllers;

use Tracks\ActionController\Base;
use App\Models\Post;

class PostsController extends Base
{
    public function index(): void
    {
        $this->set('posts', Post::published()->orderBy('created_at', 'DESC')->get());
    }
    
    public function show(): void
    {
        $post = $this->findPost();
        $this->set('post', $post);
        $this->set('comments', $post->comments());
    }
    
    public function new(): void
    {
        $this->set('post', new Post());
    }
    
    public function create(): void
    {
        $post = new Post($this->postParams());
        
        if ($post->save()) {
            $this->redirectTo('/posts/' . $post->id, ['notice' => 'Post was successfully created.']);
        } else {
            $this->set('post', $post);
            $this->render('new');
        }
    }
    
    public function edit(): void
    {
        $this->set('post', $this->findPost());
    }
    
    public function update(): void
    {
        $post = $this->findPost();
        $post->fill($this->postParams());
        
        if ($post->save()) {
            $this->redirectTo('/posts/' . $post->id, ['notice' => 'Post was successfully updated.']);
        } else {
            $this->set('post', $post);
            $this->render('edit');
        }
    }
    
    public function destroy(): void
    {
        $post = $this->findPost();
        $post->destroy();
        $this->redirectTo('/posts', ['notice' => 'Post was successfully deleted.']);
    }
    
    private function findPost(): Post
    {
        $post = Post::find($this->param('id'));
        
        if (!$post) {
            throw new \Exception('Post not found');
        }
        
        return $post;
    }
    
    private function postParams(): array
    {
        return $this->requireParams('post', ['title', 'content', 'author', 'published']);
    }
}