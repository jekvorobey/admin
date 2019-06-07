<?php

namespace App\Http\Controllers;

class MainController extends Controller
{
    public function home() {
        $this->breadcrumbs = 'home';
        return $this->render('Index', [
            'message' => 'Banana!'
        ]);
    }
}