<?php

namespace App\Controllers;

use App\Models\KomikModel;
use CodeIgniter\CodeIgniter;
use CodeIgniter\Validation\Rules;

class Komik extends BaseController
{
    protected $komikModel;
    public function __construct()
    {
        $this->komikModel = new KomikModel();
    }
    public function index()
    {
        // //tidak dipakai lagi karena 
        // $komik = $this->komikModel->findAll();
        $data = [
            'title' => 'Daftar Komik',
            'komik' => $this->komikModel->getKomik()
        ];

        // //cara connect db tanpa model
        // $db=\Config\Database::connect();
        // $komik= $db->query("SELECT*FROM komik");
        //     //kalo pengen liat datanya dah ada atau blm dump& die:
        //     //dd($komik);
        // foreach ($komik -> getResultArray() as $row) {
        //     d($row);
        // }

        //pakai ini kalo line ke 5 gak ada
        // $komikModel=new \App\Models\KomikModel()

        //pakai ini kalo di line ke 5 dah ada pemanggilan KomikModel
        // $komikModel=new KomikModel();


        return view('komik/index', $data);
    }

    public function detail($slug)
    {
        $data = [
            'title' => 'Detail Komik',
            'komik' => $this->komikModel->getKomik($slug)
        ];
        //jika komik tidak ada di tabel
        if (empty($data['komik'])) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Judul komik ' . $slug . ' tidak ditemukan :(');
        }
        return view('komik/detail', $data);
    }

    public function create()
    {
        $data = [
            'title' => 'Form Tambah Data Komik',
            'validation' => \Config\Services::validation()
        ];
        return view('komik/create', $data);
    }

    public function save()
    {

        //validasi input
        if (!$this->validate([
            'judul' => [
                'rules' => 'required|is_unique[komik.judul]',
                'errors' => [
                    'required' => '{field} komik harus diisi !',
                    'is_unique' => '{field} komik sudah terdaftar !'
                ]
            ],
            'sampul' => [
                'rules' => 'max_size[sampul,1024]|is_image[sampul]|mime_in[sampul,image/jpg,image/jpeg,image/png]',
                'errors' => [
                    'max_size' => 'Ukuran gambar terlalu besar',
                    'is_image' => 'Yang anda pilih bukan gambar',
                    'mime_in' => 'Yang anda pilih bukan gambar',

                ]
            ]
        ])) {
            ////kayak gini bisa
            // $validation = \Config\Services::validation();
            // return redirect()->to('/komik/create')->withInput()->with('validation', $validation);

            //kayak gini juga bisa
            return redirect()->to('/komik/create')->withInput();
        }

        // //untuk melihat datanya sudah diambil apa blm 
        // dd($this->request->getVar());

        //untuk ambil gambarnya
        $fileSampul = $this->request->getFile('sampul');
        //cek apakah tidak ada gambar yang diupload
        if ($fileSampul->getError() == 4) {
            $namaSampul = 'default.png';
        } else {
            //generate nama sampul random
            $namaSampul = $fileSampul->getRandomName();
            //untuk memindahkan file ke folder img
            $fileSampul->move('img', $namaSampul);
            // //ambil nama file sampul, dipakai kalo pengen nama file yang di database sama dengan nama file yang diupload
            // $namaSampul = $fileSampul->getName();
        }

        //untuk mengubah slug jadi judul dengan huruf kecil semua dan yang ada spasi diganti sama tanda "-"
        $slug = url_title($this->request->getVar('judul'), '-', true);
        $this->komikModel->save([
            'judul' => $this->request->getVar('judul'),
            'slug' => $slug,
            'penulis' => $this->request->getVar('penulis'),
            'penerbit' => $this->request->getVar('penerbit'),
            'sampul' => $namaSampul
        ]);

        session()->setFlashdata('pesan', 'Data berhasil ditambahkan.');

        return redirect()->to('/komik');
    }
    public function delete($id)
    {
        //cari gambar berdasarkan id
        $komik = $this->komikModel->find($id);
        //cek jika file gambarnya default.png
        if ($komik['sampul'] != 'default.png') {
            //hapus gambar yg ada di folder
            unlink('img/' . $komik['sampul']);
        }


        $this->komikModel->delete($id);

        session()->setFlashdata('pesan', 'Data berhasil dihapus.');

        return redirect()->to('/komik');
    }

    public function edit($slug)
    {
        $data = [
            'title' => 'Form Edit Data Komik',
            'validation' => \Config\Services::validation(),
            'komik' => $this->komikModel->getKomik($slug)
        ];
        return view('komik/edit', $data);
    }

    public function update($id)
    {
        //cek judul dulu
        $komikLama = $this->komikModel->getKomik($this->request->getVar('slug'));
        if ($komikLama['judul'] == $this->request->getVar('judul')) {
            $rule_judul = 'required';
        } else {
            $rule_judul = 'required|is_unique[komik.judul]';
        }
        //validasi input
        if (!$this->validate([
            'judul' => [
                'rules' => $rule_judul,
                'errors' => [
                    'required' => '{field} komik harus diisi !',
                    'is_unique' => '{field} komik sudah terdaftar !'
                ]
            ],
            'sampul' => [
                'rules' => 'max_size[sampul,1024]|is_image[sampul]|mime_in[sampul,image/jpg,image/jpeg,image/png]',
                'errors' => [
                    'max_size' => 'Ukuran gambar terlalu besar',
                    'is_image' => 'Yang anda pilih bukan gambar',
                    'mime_in' => 'Yang anda pilih bukan gambar',

                ]
            ]
        ])) {
            return redirect()->to('/komik/edit/' . $this->request->getVar('slug'))->withInput();
        }

        $fileSampul = $this->request->getFile('sampul');

        //cek gambar, apakah tetap gambar lama?
        if ($fileSampul->getError() == 4) {
            $namaSampul = $this->request->getVar('sampulLama');
        } else {
            //generate nama file random
            $namaSampul = $fileSampul->getRandomName();
            //pindahkan gambar
            $fileSampul->move('img', $namaSampul);
            //hapus file yang lama
            unlink('img/' . $this->request->getVar('sampulLama'));
        }

        $slug = url_title($this->request->getVar('judul'), '-', true);
        $this->komikModel->save([
            'id' => $id,
            'judul' => $this->request->getVar('judul'),
            'slug' => $slug,
            'penulis' => $this->request->getVar('penulis'),
            'penerbit' => $this->request->getVar('penerbit'),
            'sampul' => $namaSampul
        ]);

        session()->setFlashdata('pesan', 'Data berhasil diedit.');

        return redirect()->to('/komik');
    }


    //--------------------------------------------------------------------

}
