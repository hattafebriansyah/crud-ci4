<?php

namespace App\Controllers;

class Pages extends BaseController
{
    public function index()
    {
        $data = [
            'title' => 'Home | HattaWeb',
            'tes' => ['satu', 'dua', 'tiga']
        ];

        echo view('pages/home', $data);

    }

    public function about()
    {
        $data = [
            'title' => 'About | HattaWeb'
        ];
        return view('pages/about',$data);
    }

    public function contact()   
     {
        $data = [
            'title' => 'Contact | HattaWeb',
            'alamat'=>[
                [
                    'tipe'=>'rumah',
                    'alamat'=>'Jalan anu No.123',
                    'kota'=>'Bandung'
                ],
                [
                    'tipe'=>'kantor',
                    'alamat'=>'Jalan anu No.234',
                    'kota'=>'Padang'
                ],
            ]
        ];
        return view('pages/contact',$data);
    }

    //--------------------------------------------------------------------

}
