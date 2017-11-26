<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller {

	private $modelBigram;
	private $modelUnigram;
	private $modelPOS;

	public function __construct(){
		parent::__construct();
		$this->modelBigram = array();
		$this->modelUnigram = array();
		$this->modelPOS = array();
	}
	public function first(){
		ini_set('max_execution_time',0);
		$get = $this->Tools->praProcess(file_get_contents(FCPATH.'datasets/idn-tagged-corpus-master/Indonesian_Manually_Tagged_Corpus.tsv'));
		// $i=1;
		// var_dump(count($get));	
		foreach($get as $insert){
			// $get[$key]['id'] = $i;
			// var_dump($get);
			$this->ModelVocab->insert($insert);
			// $i++;
		}
	}
	public function second(){
		$data = $this->ModelVocab->selectAll()->result_array();
		$this->modelPOS = $this->Tools->posCount($data,$this->modelPOS);
		// $tP = $this->Tools->transitionProb($data,$this->modelPOS);
		// $eP = $this->Tools->emissionProb($data,$this->modelPOS);
		// $this->Tools->pre_print_r($eP);
	}
	public function index(){
		ini_set('memory_limit',"256M");
		$this->second();
		$data['numOfToken'] = $this->ModelVocab->selectAll()->num_rows();
		$data['numOfTag'] = count($this->modelPOS)-1;
		$data['tagset'] = $this->ModelTagset->selectAll()->result_array();

		$data['topTransition'] = $this->Tools->transitionProb($this->ModelVocab->selectAll()->result_array(),$this->modelPOS);
		usort($data['topTransition'], function ($a, $b) {
			return $a['count'] < $b['count'];
		});
		$data['topEmission'] = $this->Tools->emissionProb($this->ModelVocab->selectAll()->result_array(),$this->modelPOS);
		usort($data['topEmission'], function ($a, $b) {
			return $a['count'] < $b['count'];
		});		

		$this->load->view('home',$data);
	}

	public function ac(){
		ini_set('memory_limit',"256M");
		$this->second();
		$data['numOfToken'] = $this->ModelVocab->selectAll()->num_rows();
		$data['numOfTag'] = count($this->modelPOS)-1;
		$data['tagset'] = $this->ModelTagset->selectAll()->result_array();

		$data['topTransition'] = $this->Tools->transitionProb($this->ModelVocab->selectAll()->result_array(),$this->modelPOS);
		usort($data['topTransition'], function ($a, $b) {
			return $a['count'] < $b['count'];
		});
		$data['topEmission'] = $this->Tools->emissionProb($this->ModelVocab->selectAll()->result_array(),$this->modelPOS);
		usort($data['topEmission'], function ($a, $b) {
			return $a['count'] < $b['count'];
		});		

		$post = $this->input->post();
		$data['res'] = $this->Tools->ambiguChecker($post['word'],$data['topEmission']);
		$this->Tools->pre_print_r($data['res']);
		// $this->load->view('ac',$data);
	}

	public function pt(){
		ini_set('memory_limit',"256M");
		$this->second();
		$data['numOfToken'] = $this->ModelVocab->selectAll()->num_rows();
		$data['numOfTag'] = count($this->modelPOS);
		$data['tagset'] = $this->ModelTagset->selectAll()->result_array();
		$realTr = $this->Tools->transitionProb($this->ModelVocab->selectAll()->result_array(),$this->modelPOS);
		$data['topTransition'] = $realTr;
		usort($data['topTransition'], function ($a, $b) {
			return $a['count'] < $b['count'];
		});
		$realEm = $this->Tools->emissionProb($this->ModelVocab->selectAll()->result_array(),$this->modelPOS);
		$data['topEmission'] = $realEm;
		usort($data['topEmission'], function ($a, $b) {
			return $a['count'] < $b['count'];
		});
		$post = $this->input->post();
		$data['tagz'] = $this->Tools->viterbi($post['sentence'],$realTr,$realEm);
		$data['sentence'] = explode(" ",$post['sentence']);

		$this->load->view('pt',$data);
	}
	
}
