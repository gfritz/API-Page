<?php

class TwitterDB{
	private $dbName = 'twitter';
	private $host = 'localhost';
	private $dbPass = 'votey332';
	private $user = 'scott';
	private $tableName = 'tbl_tweet';
	public $dsn = null;	// for PDO	
	private $con = null;
	public $error = false;


	public function __construct(){
		$this->dsn = "mysql:dbname=".$this->dbName.";host=".$this->host;
		$this->dbConnect();
	}


	//connect to the database
	public function dbConnect(){
		try{
			$this->con = new PDO($this->dsn, $this->user, $this->dbPass);
			$this ->con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->con->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
			$this->error = false;
		}catch(PDOException $err){$this->error = true;}
	}


	//close the connection to the databsee
	public function dbDisconnect(){
		$this->con=null;
	}


	//Insert tweets into the database
	//input: array of tweets direct from twitter
	//		 search_string generated by TwitterQuery class
	//
	//When pulling from twitter using json,
	//if $tweets is object holding return from query  
	//pass to this object $tweets->results
	public function insertTweets($tweets, $search_string){
		if(!$this->error){
			foreach ($tweets as $tweet) {
				$id_str = $tweet->id_str;
				$from_user_id = $tweet->from_user_id;
				$text = $tweet->text;
				if($tweet->geo!=null){
					$lat = $tweet->geo->coordinates[0];
					$long =$tweet->geo->coordinates[1];
				}else{
					$lat = null;
					$long = null;
				}
				$created_at = $this->tweetdate($tweet->created_at);
				if(!$this->tweetExists($id_str)){
					$db_query = 'INSERT INTO '.$this->tableName.' (from_user_id, id_str, latitude, longitude, text, created_at, search_str) VALUES (?,?,?,?,?,?,?)';
					$data = array($from_user_id, $id_str, $lat, $long, $text, $created_at, $search_string);
					$insertStmt = $this->con->prepare($db_query);
					$insertStmt->execute($data);				
				}
			}
		}else{
			echo 'No connection to database was established. Verify credentials.';
		}
	}

	//Update tweets to whether they indicate a danger zone or not.
	//input: tweet id 
	//       danger level 0= not dangerous
	//                    1= dangerous
	public function updateTweetDanger($id_str, $danger_level){
		if(!$this->error){
			$db_query = 'UPDATE '.$this->tableName.' set danger=? WHERE id_str = ?';
			$input = array($danger_level, $id_str);
			$updateStmt = $this->con->prepare($db_query);
			$updateStmt->execute($input);
		}else{
			echo 'No connection to database was established. Verify credentials.';
		}
	}


	//grabs all the tweets we have stored in the database
	public function selectTweets(){
		if(!$this->error){		
			$selectQuery = 'SELECT * FROM '.$this->tableName;
			$selectStatement =$this->con->prepare($selectQuery);
			$selectStatement->execute();
			return $selectStatement->fetchall();
		}else{
			echo 'No connection to database was established. Verify credentials.';
			return null;
		}
	}


	//grabs the stored tweet information by the specific id string
	public function selectTweetByID($id_str){
		if(!$this->error){	
			$selectQuery = 'SELECT * FROM '.$this->tableName.' WHERE id_str='.$id_str;
			$selectStatement =$this->con->prepare($selectQuery);
			$selectStatement->execute();
			return $selectStatement->fetchall();
		}else{
			echo 'No connection to database was established. Verify credentials.';
			return null;
		}
	}


	//grabs stored tweet information by specific user id
	public function selectTweetByUserID($from_user_id){
		if(!$this->error){
			$selectQuery = 'SELECT * FROM '.$this->tableName.' WHERE from_user_id='.$from_user_id;
			$selectStatement =$this->con->prepare($selectQuery);
			$selectStatement->execute();
			return $selectStatement->fetchall();
		}else{
			echo 'No connection to database was established. Verify credentials.';
			return null;
		}
	}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////FUNCTIONS USED BY insertTweets//////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////	

	//check the data base to see if a tweet already exists in there
	//input: id string of a tweet
	private function tweetExists($id_str){
		$exists= false;
		$existsQuery = 'SELECT * FROM '.$this->tableName.' WHERE id_str='.$id_str;
		$existsStatement = $this->con->prepare($existsQuery);
		$existsStatement ->execute();
		$tweet_id = $existsStatement ->fetchall();
		if(count($tweet_id)!=0){
			$exists = true;
		}
		return $exists;
	}

	//converts the date string given back by a tweet query into a datetime mysql format
	//input: tweet date string
	private function tweetDate($date_string){
		$parsed_date = explode(' ', $date_string);
		$day = $parsed_date[1];
		$month = $this->numericMonth($parsed_date[2]);
		$year = $parsed_date[3];
		$time = $parsed_date[4];
		$date = implode(' ',array(implode('-',array($year,$month,$day)),$time));
		return $date;
	}


	//converts a string month to an int
	//input: string
	private function numericMonth($month_string){
		$month='01';
		switch($month_string){
			case "Jan":
				break;
			case "Feb":
				$month='02';
				break;
			case "Mar":
				$month='03';
				break;
			case "Apr":
				$month='04';
				break;
			case "May":
				$month='05';
				break;
			case "Jun":
				$month='06';
				break;
			case "Jul":
				$month='07';
				break;
			case "Aug":
				$month='08';
				break;
			case "Sep":
				$month='09';
				break;
			case "Oct":
				$month='10';
				break;
			case "Nov":
				$month='11';
				break;
			case "Dec":
				$month='12';
				break;
		}
		return $month;
	}

}