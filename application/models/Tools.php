<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tools extends CI_Model {

	public function index(){
		parent::__construct();
	}
	public function pre_print_r($data){
		echo "<pre>";
		print_r($data);
		echo "</pre>";
	}
	public function praProcess($data){
		$data = preg_split('/[\n,]+/',$data);

		foreach($data as $i){
			$temp = explode("\t",$i);
			if(isset($temp[1])){
				$pos['word'] = strtolower($temp[0]);
				$pos['pos'] = $temp[1];
				$fix[] = $pos;
			}
			
		}
		// $this->pre_print_r($fix);
		return $fix;
	}

	public function transitionProb($sentences,$poscount){
		$indexes = array();
		for($i=0;$i<count($sentences)-1;$i++){
			$key=$sentences[$i+1]['pos']."|".$sentences[$i]['pos'];
			if(array_key_exists($key,$indexes)){
				$indexes[$key]["count"] ++;
			}else{
				$indexes[$key] = array(
					'count' => 1,
					'key' => $key,
					'prob'=> 0,
					);
			}
		}
		foreach($indexes as $key => $row){
			$indexes[$key]['prob'] = $row['count']/$poscount[explode("|",$row['key'])[1]]['count'];
		}
		return $indexes;
	}

	public function emissionProb($sentences,$poscount){
		$indexes = array();
		for($i=0;$i<count($sentences);$i++){
			$key=$sentences[$i]['word']."|".$sentences[$i]['pos'];
			if(array_key_exists($key,$indexes)){
				$indexes[$key]["count"] ++;
			}else{
				$indexes[$key] = array(
					'count' => 1,
					'key' => $key,
					'prob' => 0,
					);
			}
		}
		foreach($indexes as $key => $row){
			$indexes[$key]['prob'] = $row['count']/$poscount[explode("|",$row['key'])[1]]['count'];
		}
		return $indexes;
	}

	public function posCount($data,$indexes){
		for($i=0;$i<count($data);$i++){
			$key = $data[$i]['pos'];
			if(array_key_exists($data[$i]['pos'],$indexes)){
				$indexes[$key]["count"]++;
			}else{
				$indexes[$key] = array(
				  'count' => 1,
				  'words' => $key,
				);
			}
		}
		// $this->pre_print_r($indexes);
		return $indexes;
	}

	public function viterbi($string,$tr,$em){
		$POS = array();
		$indexes = array();
		$string = explode(" ",$string);
		/*Looping string*/
		for($i=0;$i<count($string);$i++){
			if($i==0){
				$b4[] = "q0";
				$vb4[] = 1;
			}else{
				$b4 = $temp;
				$vb4 = $tempvb4;
				unset($temp);
				unset($tempvb4);
			}
			/*Looping macam2 tag pada setiap string*/
			foreach($em as $key => $j){
				/*Cari string ada tag apa aja*/
				if(strtolower($string[$i]) == strtolower(explode("|",$em[$key]['key'])[0])){
					/*Looping sebanyak nilai sebelumnya*/
					unset($res);
					for($k=0;$k<count($b4);$k++){
						// echo "B-".$vb4[$k]; 
						// echo $string[$i]." | ".$em[$key]['key']." | ".$b4[$k]." | ".$tr[explode("|",$key)[1]."|".$b4[$k]]['key'];
						if(!isset($tr[explode("|",$key)[1]."|".$b4[$k]])){
							$res[] = $em[$key]['prob'] * 0 * $vb4[$k];
						}else{
							$res[] = $em[$key]['prob'] * $tr[explode("|",$key)[1]."|".$b4[$k]]['prob'] * $vb4[$k];
						}
						// var_dump($tr[explode("|",$em[$key]['key'])[1]."|".$b4[$k]]);
					}
					$temp[] = explode("|",$em[$key]['key'])[1];
					$tempvb4[] = max($res);
					// echo "<br/>";
				}
			}
			// var_dump($temp);
			if(!isset($tempvb4) && !isset($temp)){
				$POS[] = "X";
				$temp[] = "q0";
				$tempvb4[] = 1;
			}else{
				$POS[] = $temp[array_keys($tempvb4, max($tempvb4))[0]];
			}
		}
		return $POS;
	}

	public function ambiguChecker($word, $data){
		foreach($data as $row){
			if(strtolower($word) == strtolower(explode("|",$row['key'])[0])){
				$res[] = $row;
			}		
		}
		return $res;
	}
}