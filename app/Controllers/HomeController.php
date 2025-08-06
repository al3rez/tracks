<?php

namespace App\Controllers;

use Tracks\ActionController\Base;

class HomeController extends Base
{
    public function index(): void
    {
        $this->set('title', 'Welcome to Tracks');
        $this->set('message', 'A Ruby on Rails-like PHP Framework');
    }
}