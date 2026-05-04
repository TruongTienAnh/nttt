<?php
namespace App\Controllers;

class HomeController extends BaseController 
{
    public function Index() 
    {
        // Lấy thông tin user (giống hệt cách bạn làm ở route)
        $user = app()->request->user; 
        
        // Vì class này đã extends BaseController, nó đã tự động 
        // nạp sẵn $this->branches, $this->branchId... cho layout app.php rồi!
        return view('home/home', ['user' => $user]);
    }
}