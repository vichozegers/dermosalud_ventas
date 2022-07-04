<?php
    require_once('traits/uniquecheck.php');
    class Sale extends ActiveRecord\Model {

        public static $table_name = 'zarest_sales';
        use uniquecheck;
        public function validate() {
            $this->uniquecheck(array('code','message' => 'Can\'t have duplicate code.'));
        }
    }
