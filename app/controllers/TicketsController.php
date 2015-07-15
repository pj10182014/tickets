<?php

class TicketsController extends BaseController {

	/*
	|--------------------------------------------------------------------------
	| Default Home Controller
	|--------------------------------------------------------------------------
	|
	| You may wish to use controllers instead of, or in addition to, Closure
	| based routes. That's great! Here is an example controller method to
	| get you started. To route to this controller, just add the route:
	|
	|	Route::get('/', 'HomeController@showWelcome');
	|
	*/

	public function getIndex()
	{	
		$num =	$this->fileConvertAndUpdate();
		return View::make('ticket',array('num'=>$num));
		//$this->fileConvertAndUpdate();
	}

	public function fileConvertAndUpdate(){

		$dir = "../files/";
		$num = 0;
		$documents_group = scandir($dir);

		foreach ($documents_group as $key => $value) {
			if($value != "." && $value != ".."){
				$handler = new handler($dir,$value);
				if($handler->getFileType() != NULL){
					try {
			            $document = Document::create(array(
			                'path' 			=> $handler->getPath(),
			                'fileName'  	=> $handler->getFileName(),
			                'fileType'  	=> $handler->getFileType(),
			                'systemName'    => $handler->getSystemName(),
			                'airlineName'   => $handler->getAirlineName(),
			                'ticketNumber'  => $handler->getTicketNumber(),
			                'dateString'    => $handler->getDateString(),
			                'orderOfDay'    => $handler->getOrderOfDay(),
			                'fileContent'   => $handler->getFileContent(),
			                'dateOfFile'    => $handler->getDateOfFile(),
			                'paxName'		=> $handler->getPaxName(),
			                'rloc'			=> $handler->getRloc(),
			                'ticketsType'	=> $handler->getTicketsType(),
			            ));
			            $document->save();
			            $num++;
			            rename($dir.$value, "../done/".$value);
			        } catch (Exception $e) {
			            $response['info'] = "fail";
			            $boolean = false;
			            echo $e;
			        }
		        }				
			}
		}

		//echo $num." files have been converted."; die;
		return $num;
	}

	public function update(){
		$data = array();
		$num = $this->fileConvertAndUpdate();
		$data['num'] = $num;
		echo json_encode($data);
	}

	public function search(){
		$data = array();
		if(($_POST['ticketNumber'] != null) || (is_numeric($_POST['ticketNumber']))){
			$ticketNumber = trim($_POST['ticketNumber']);
		}else{
			$ticketNumber = "";
		}

		if (($_POST['passengerName'] != null)) {
			$passengerName = strtoupper(trim($_POST['passengerName']));
		}else{
			$passengerName = null;
		}

		if (($_POST['rloc'] != null)) {
			$rloc = strtoupper(trim($_POST['rloc']));
		}else{
			$rloc = "";
		}

		$parsePassengerName = explode(" ", $passengerName);

		$first = (array_key_exists(0, $parsePassengerName) ? $parsePassengerName[0] : "");
		$mid   = (array_key_exists(1, $parsePassengerName) ? $parsePassengerName[1] : "");
		$last   = (array_key_exists(2, $parsePassengerName) ? $parsePassengerName[2] : "");

		if(strlen($ticketNumber) == 10 ){
			$model = Document::where('ticketNumber', '=', $ticketNumber)->get();	
		}elseif(strlen($rloc) == 6 ){
			$model = Document::where('rloc', '=', $rloc)->get();
		}else{
			$model = Document::where('paxName', 'LIKE', '%'.$first.'%')
							 ->where('paxName','LIKE','%'.$mid.'%')
							 ->where('paxName','LIKE','%'.$last.'%')
							 ->where('ticketNumber', 'LIKE', '%'.$ticketNumber.'%')
							 ->where('rloc', 'LIKE', '%'.$rloc.'%')
							 ->get();	
		}
		//$model = Document::where('tickeNumebr', '=', $ticketNumber)->first();
		
		$index = 0;
		if(sizeof($model)>0){
			foreach ($model as $key => $value) {
				$document = $value->getAttributes();
				//if($document){
					$data[$index]['content']=$document['fileContent'];
					$data[$index]['dateOfFile']=$document['dateOfFile'];
					$data[$index]['paxName']=$document['paxName'];
					$data[$index]['airlineName']=$document['airlineName'];
					$data[$index]['orderOfDay']=$document['orderOfDay'];
					$data[$index]['ticketNumber']=$document['ticketNumber'];
				//}else{
					//$data['content'][]="Sorry the document does not exist, or hasn't been update yet, please click update and try again.";					
				//}
				$index++;
			}
			//$document = $model[0]->getAttributes();
			//$data['content'] = $document['fileContent']; 	
		}else{
			$data[$index]['content'] = "Sorry the document does not exist, or hasn't been update yet, please click update and try again."; 	
		}
		echo json_encode($data);
	}

	public function previous(){

	}


	/**
	 * function next()
	 * Searching the database to find the dateOfFile (2015-06-25) and the orderOfDay
	 * Check the maximum orderOfDay there are on the same date
	 * Passes content, orderOfDay and maxOrderNumber to the view (ticket.blade.php)
     */
	public function next(){
		$data = array();
		$nextOrderOfDay = $_POST['orderOfDay'] + 1;
		$dateOfFile = $_POST['dateOfFile'];

		$model = Document::where('orderOfDay', '=', $nextOrderOfDay)->where('dateOfFile', '=', $dateOfFile)->get();
		$model_orderOfDay = Document::where('dateOfFile', '=', $dateOfFile)->get();

		$allOrderNumbers = array();
		foreach($model_orderOfDay as $key => $value){
			$allOrderNumbers[] = $value->orderOfDay;
		}
		$maxOrderNumber = max($allOrderNumbers);

		if($nextOrderOfDay > $maxOrderNumber){
			$data['content'] = '<p>Reached MAX record<br>Total records for the day: ' . $maxOrderNumber . '</p>';
			$data['maxOrderNumber'] = $maxOrderNumber;
		}else{
			$data['content'] = $model[0]->fileContent;
			$data['orderOfDay'] = $model[0]->orderOfDay;
		}
		echo json_encode($data);
	}
}