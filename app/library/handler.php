<?php
	class Handler {

        private $path;
        private $fileName;
        private $fileType;
        private $systemName;
        private $airlineName;
        private $ticketNumber;
        private $dateString;
        private $orderOfDay;
        private $dateOfFile;
        private $fileContent;
        private $rloc;
        private $paxName;
        private $ticketsType;

        public function __construct($path,$fileName){

            $this->path     = $path;
            $this->fileName = $fileName; 
            //$this->parseFile($path, $fileName);
            $this->convertFile($path, $fileName);
        }

        //getters
        public function getPath(){
            return $this->path;
        }

        public function getFileName(){
            return $this->fileName;
        }

        public function getFileType(){
            return $this->fileType;
        }

        public function getSystemName(){
            return $this->systemName;
        }

        public function getAirlineName(){
            return $this->airlineName;
        }

        public function getTicketNumber(){
            return $this->ticketNumber;
        }

        public function getDateString(){
            return $this->dateString;
        }

        public function getOrderOfDay(){
            return $this->orderOfDay;
        }

        public function getDateOfFile(){
            return $this->dateOfFile;
        }

        public function getFileContent(){
            return $this->fileContent;
        }

        public function getRloc(){
            return $this->rloc;
        }

        public function getPaxName(){
            return $this->paxName;
        }

        public function getTicketsType(){
            return $this->ticketsType;
        }

        //setters
        public function setFileName($fileName){
            $this->fileName = $fileName;
        }

        public function setFileType($fileType){
            $this->fileType = $fileType;
        }

        public function setSystemName($systemName){
            $this->systemName = $systemName;
        }

        public function setAirlineName($airlineName){
            $this->airlineName = $airlineName;
        }

        public function setTicketNumber($ticketNumber){
            $this->ticketNumber = $ticketNumber;
        }

        public function setDateString($dateString){
            $this->dateString = $dateString;
        }

        public function setOrderOfDay($orderOfDay){
            $this->orderOfDay = $orderOfDay;
        }

        public function setDateOfFile($dateOfFile){
            $this->dateOfFile = $dateOfFile;
        }

        public function setFileContent($fileContent){
            $this->fileContent = $fileContent;
        }

        public function setRloc($rloc){
            $this->rloc = $rloc;
        }

        public function setPaxName($paxName){
            $this->paxName = $paxName;
        }

        public function setTicketsType($ticketsType){
            $this->ticketsType = $ticketsType;
        }


        public function convertFile($path,$fileName){
            $ticketFileNeedle = "**ITINERARY**";
            $refundFileNeedle1 = "*****REFUNDED TICKETS";
            $refundFileNeedle2 = "***REFUNDED TICKETS";
            $refundFileNeedle3 = "REFUND NOTICE";
            $refundType = array();
            $myfile = fopen($path.$fileName, "r") or die("Unable to open file!");
            
            $fileString = file_get_contents($path.$fileName);

            $isTicket = strpos($fileString, $ticketFileNeedle);
            if($isTicket > 0 ){
                $this->parseTicket($path,$fileName);   
            }
            
            $refundType['1A'] = strpos($fileString, $refundFileNeedle1);
            $refundType['1V'] = strpos($fileString, $refundFileNeedle2);
            $refundType['AA'] = strpos($fileString, $refundFileNeedle3);

            $isRefund = strpos($fileString, $refundFileNeedle1) + strpos($fileString, $refundFileNeedle2)+ strpos($fileString, $refundFileNeedle3);
            if($isRefund > 0){
                $this->parseRefunded($path,$fileName,$refundType);
            }
        }

        public function parseTicket($path, $fileName){

            $fileLines = array();
            $typeLine   = array();
            $numberLine = array();
            $parsedLine = array();

            $typeNeedle = "1 OF 1";
            $numberNeedle = "PRI FF";

            //parse file name;
            $temp = preg_replace("/[^0-9]/", "", $fileName);
            
            $dateString = substr($temp, 0,8);
            $orderOfDay = substr($temp,8,2);

            $this->setDateString($dateString);
            $this->setOrderOfDay($orderOfDay);

            $date = new DateTime($dateString);
            $this->setDateOfFile($date->format('Y-m-d'));

            //parse file content into whole string;
            $fileContent = "<pre>"; 
            $fileContent .= file_get_contents($path.$fileName);
            $fileContent .= "</pre>";

            $this->setFileContent($fileContent);

            //parse file to get all the details
            $myfile = fopen($path.$fileName, "r") or die("Unable to open file!");
            while(!feof($myfile))
            {
                $fileLines[] = fgets($myfile);
            }
            fclose($myfile);

            foreach ($fileLines as $key => $line) {
                $posType = strpos($line, $typeNeedle);
                $posNumber = stripos($line, "PRI FF") + stripos($line, "FFFF") + stripos($line,"FFVV") ;

                if($posType > 0){
                    $typeLine = explode("  ",trim($line));
                    $nameLineIndex = $key + 1;
                }
                if($posNumber > 0){
                    $numberLine = explode(" ",trim($line));
                }
            }

            $nameLineArray = explode("  ", $fileLines[$nameLineIndex]);
            $paxName = str_replace("/", " ", trim($nameLineArray[0]));
            $this->setPaxName($paxName);

            foreach ($typeLine as $key => $value) {
                if(strpos($value, "/") > 0){
                    $value = explode("/",trim($value));
                    $rloc  = $value[0];
                    $type  = $value[1];
                    $this->setRloc($rloc);
                    $this->setFileType($type);
                    //$parsedLine['type'] = $type;
                    switch ($type) {
                        case '1A':
                            $this->setSystemName("AMADEUS");
                            //$parsedLine['systemName'] = "AMADEUS";
                            break;
                        case '1V':
                            $this->setSystemName("GALILEO");
                            //$parsedLine['systemName'] = "GALILEO";
                            break;
                        case 'AA':
                            $this->setSystemName("SABRE");
                            //$parsedLine['systemName'] = "SABRE";
                            break;
                        case '1S':
                            $this->setSystemName("SABRE");
                            //$parsedLine['systemName'] = "Unknown";
                            break;
                    }
                }
            }
            $this->setAirlineName($typeLine[0]);
            //$parsedLine['airlineName'] = $typeLine[0];
            
            foreach ($numberLine as $key => $value) {
                if(strlen($value) == 10){
                    //$parsedLine['tickeNumebr'] = $value;
                    $this->setTicketNumber($value);
                }
            }

            $this->setTicketsType("Ticket");
        }

        public function parseRefunded($path,$fileName,$refundType){

            $fileLines = array();
            $numberLine = array();
            $airLine = array();
            $nameLine = array();

            //parse file name;
            $temp = preg_replace("/[^0-9]/", "", $fileName);
            
            $dateString = substr($temp, 0,8);
            $orderOfDay = substr($temp,8,2);

            $this->setDateString($dateString);
            $this->setOrderOfDay($orderOfDay);

            $date = new DateTime($dateString);
            $this->setDateOfFile($date->format('Y-m-d'));

            //parse file content into whole string;
            $fileContent = "<pre>"; 
            $fileContent .= file_get_contents($path.$fileName);
            $fileContent .= "</pre>";

            $this->setFileContent($fileContent);

            //parse file to get all the details
            $myfile = fopen($path.$fileName, "r") or die("Unable to open file!");
            while(!feof($myfile))
            {
                $fileLines[] = fgets($myfile);
            }
            fclose($myfile);

            foreach($refundType as $key => $value){
                if($value > 0){
                     $type  = $key;
                     switch ($type) {
                        case '1A':
                            $this->setSystemName("AMADEUS");
                            //$parsedLine['systemName'] = "AMADEUS";
                            break;
                        case '1V':
                            $this->setSystemName("GALILEO");
                            //$parsedLine['systemName'] = "GALILEO";
                            break;
                        case 'AA':
                            $this->setSystemName("SABRE");
                            //$parsedLine['systemName'] = "SABRE";
                            break;
                    }
                }
            }

            foreach ($fileLines as $key => $line) {
                
            }

            $this->setRloc("Unknown");
            $this->setTicketsType("Refund");
        }

        public function parseExchange($path,$fileName){

        }

    }
?>