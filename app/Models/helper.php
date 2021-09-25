<?php
if(!function_exists('get_settings')) {
 
    function get_settings($key) {
        $data = \DB::table('tbl_settings')
            ->select($key)
            ->where('id',1)
            ->first();
		if($data){
			return $data->$key;
		}else{
			return '';
		}
    }
}
?>