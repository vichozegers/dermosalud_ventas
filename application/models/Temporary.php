<?php
    require_once('traits/uniquecheck.php');
class Temporary extends ActiveRecord\Model {

   public static $table_name = 'zarest_temporary';
    use uniquecheck;
    public function validate() {
        $this->uniquecheck(array('code','message' => 'Can\'t have duplicate code.'));
    }
}
