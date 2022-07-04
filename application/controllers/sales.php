<?php
    defined('BASEPATH') or exit('No direct script access allowed');

    class Sales extends MY_Controller
    {

        function __construct()
        {
            parent::__construct();
            if (!$this->user) {
                redirect('login');
            }
            $this->register = $this->session->userdata('register') ? $this->session->userdata('register') : FALSE;
            $this->store = $this->session->userdata('store') ? $this->session->userdata('store') : FALSE;
        }

        public function index()
        {
            $this->view_data[ 'register' ] = $this->register;
            $this->content_view = 'sale';
            if ($this->register) {
                $waiters = Waiter::find('all', array ( 'conditions' => array ( 'store_id = ?', $this->store ) ));
                $this->view_data[ 'waiters' ] = $waiters;
            } else {
                $waiters = '';
            }
        }

        public function importcsv()
        {
            $config[ 'upload_path' ] = './files/sales';
            $config[ 'allowed_types' ] = 'csv';
            $config[ 'overwrite' ] = TRUE;
            $config[ 'max_size' ] = '500';

            $this->load->library('upload', $config);
            if ($this->upload->do_upload()) {
                $data = array (
                    'upload_data' => $this->upload->data()
                );
                $file = $data[ 'upload_data' ][ 'file_name' ];

                $fileopen = fopen('files/sales/' . $file, "r");
                if ($fileopen) {
                    while (($row = fgetcsv($fileopen, 2075, ",")) !== FALSE) {
                        $filearray[] = $row;
                    }
                    fclose($fileopen);
                }
                array_shift($filearray);

                $fields = array (
                    'boleta',
                    'fecha',
                    'codigo',
                    'total'
                );

                $final = array ();
                foreach ($filearray as $key => $value) {
                    $products[] = array_combine($fields, $value);
                }

                date_default_timezone_set($this->setting->timezone);
                $date = date("Y-m-d H:i:s");
                Temporary::query('DELETE FROM zarest_temporary');
                foreach ($products as $prdct) {
                    $product = Product::find_by_sql("SELECT * FROM zarest_products WHERE code LIKE '%".$prdct[ 'codigo' ]."'");
                    $data = array (
                        "idtemp" => $prdct[ 'boleta' ],
                        "code" => $prdct[ 'codigo' ],
                        'idproduct' => $product[0]->id,
                        "total" => $prdct[ 'total' ],
                        'price' => $prdct[ 'total' ]*$product[0]->price,
                        'date' => $prdct[ 'fecha' ]
                    );
                    Temporary::create($data);
                }

                $sales = Temporary::find_by_sql('select idtemp,SUM(price) as total,SUM(total) AS totalitems FROM zarest_temporary GROUP BY idtemp');
                for($s=0;$s < count($sales);$s++) {
                    $data = array(
                        "client_id"=> 0,
                        "clientname" => 'Cliente sin Registrar',
                        "tax"=> '19%',
                        "discount" => '',
                        "subtotal"=> $sales[$s]->total,
                        "total" => $sales[$s]->total + round(($sales[$s]->total*0.19)),
                        "created_at" => $date,
                        "modified_at" => $date,
                        "status" => 0,
                        "created_by" => 'admin admin',
                        "totalitems" => $sales[$s]->totalitems,
                        "paid" => $sales[$s]->total + round(($sales[$s]->total*0.19)),
                        'paidmethod'=> 0,
                        "taxamount" => round(($sales[$s]->total*0.19)),
                        "discountamount"=> '',
                        "register_id" => 65,
                        "firstpayement" =>  $sales[$s]->total + round(($sales[$s]->total*0.19)),
                        "waiter_id" => 1
                    );
                    Sale::create($data);
                    $last = Sale::last();
                    Temporary::query("UPDATE zarest_temporary SET idtemp = '".$last->id."' WHERE idtemp='".$sales[$s]->idtemp."'");
                }

                $sales = Temporary::find_by_sql('select * FROM zarest_temporary');
                for($s=0;$s < count($sales);$s++) {
                    $product = Product::find_by_sql("SELECT * FROM zarest_products WHERE id = '".$sales[$s]->idproduct."'");
                    $data = array(
                        "sale_id"=> $sales[$s]->idtemp,
                        "product_id" => $sales[$s]->idproduct,
                        "name"=> $product[0]->name,
                        "price" => $product[0]->price,
                        "qt"=> $sales[$s]->total,
                        "subtotal" => $sales[$s]->price,
                        "date" => $sales[$s]->date,
                    );
                    Sale_item::create($data);
                }
                Temporary::query('DELETE FROM zarest_temporary');
            }
        }
    }
