<?php

namespace Plugins\Thesaurus;

use \Typemill\Plugin;

use \sqlite3;
use Symfony\Component\Yaml\Yaml;

class Index extends Plugin
{
  // What for?
  public static function getSubscribedEvents(){}

	public function searchDat($dbfile, $search_for)
	{
		$userSettings = \Typemill\Settings::getUserSettings();
		$language = $userSettings['language'];
    
		$meanings = [];
		$search_for = $search_for.'|';
		$length = strlen($search_for);
		$matches = array();
		$row = 0;

		$file = new \SplFileObject($dbfile);
		while (!$file->eof()) {
			$row++;
			$buffer = $file->fgets();
			if(strpos($buffer, $search_for) !== FALSE){
				if(substr($buffer,0,$length)==$search_for){
					$matches[$row] = $buffer;
				}
			}
		}
		
		if(!empty($matches)){
			$row = key($matches);
			$pieces = explode('|',$matches[$row]);
			$word = $pieces[0];
			$number = intval($pieces[1]);
			
			$file->seek($row);
			while($number != 0){

				switch ($language) {
					case 'it':
						//$meanings[] = utf8_encode($file->current());
						$meanings[] = iconv("ISO-8859-15","UTF-8",$file->current());
						break;
					default:
						$meanings[] = $file->current();
						break;
				}
    
				$number -= 1;
				$file->next();
			}
		}
		$file = null;
		
		return $meanings;
	}

	
  // Database search
  public function searchSqlite($dbfile, $search_for)
  {
    // create a SQLite3 object and open a connection to the SQLite3 database.
    $db = new SQLite3($dbfile, SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);

    // search the word to know the id
    // FOR NOW, AN IDENTICAL WORD IS BEING SOUGHT ...
    // AND THIS IS A PROBLEM IN ALMOST ALL LANGUAGES:
    // PLURALS, FEMININE, DECLENSION OF VERBS REMAIN OUTSIDE.
    $query = "SELECT * FROM words WHERE word LIKE '".$search_for."'";
    $result = $db->query( $query ) or die('Query failed');
    $row = $result->fetchArray();
    $id = $row['id'];

    // through id look for meanings
    $meanings = [];
    if(!empty($id)){
      $query = "SELECT * FROM meanings WHERE word_id='".$id."'";
      $result = $db->query( $query ) or die('Query failed');
      while ($row = $result->fetchArray()) {
        $meanings[] = $row['meaning'];
      }
    }

    // close database
    $db->close();

    return $meanings;
  }


  public function meanings()
  {
	
	$userSettings = \Typemill\Settings::getUserSettings();
    $data_base = $userSettings['plugins']['thesaurus']['data_base'];
    
    // get the word to search
    $search_for = $_GET['search_for'];
    $search_for = strtolower($search_for);
    
    // get Twig Instance and add the thesaurus template-folder to the path
    $twig = $this->getTwig();
    $loader = $twig->getLoader();
    $loader->addPath(__DIR__ . '/templates/');

    // which language
    $userSettings = \Typemill\Settings::getUserSettings();
    $language = $userSettings['language'];
    $filename = $language . '.thesaurus.'.$data_base;
    $dbfile = __DIR__ . '/data/' . $filename;

    // THIS WORKS BUT I DON'T LIKE IT AT ALL.
    // LABELS SHOULD BE AVAILABLE THROUGH RESEARCH THAT ONE IN INDEX.PHP
    $labels = \Typemill\Translations::loadTranslations('admin');


    // database does not exist
    if(!file_exists($dbfile)){
      return $twig->fetch('/nofile.twig',
        array(
          'filename'  => $filename, // string with database file name
          'labels'    => $labels    // translated strings
        )
      );
    }
    
    // start the stopwatch
    $startTimer = microtime(true);

    // data base search
    if($data_base == 'sqlite'){
		$meanings = self::searchSqlite($dbfile, $search_for);
	} else {
		$meanings = self::searchDat($dbfile, $search_for);
	}
    $count = count($meanings);

    // stop the stopwatch and get the time taken
    $stopTimer = microtime(true);
    //$execution_time = round($stopTimer - $startTimer, 7) * 1000 ." ms";
    $execution_time = round($stopTimer - $startTimer, 7) * 1000;
    
    // twig for meanings not found
    if(empty($count)){
      return $twig->fetch('/nomeanings.twig',
        array(
          'search_for'      => $search_for,     // string containing the searched word
          'execution_time'  => $execution_time, // string of time taken
          'filename'        => $filename,       // string with database file name
          'labels'          => $labels          // translated strings
        )
      );
    }

    // twig for meanings found
    return $twig->fetch('/meanings.twig',
      array(
        'search_for'      => $search_for,     // string containing the searched word
        'meanings'        => $meanings,       // array of meanings 
        'execution_time'  => $execution_time, // string of time taken
        'filename'        => $filename,       // string with database file name
        'count'           => $count,          // number of meanings found
        'labels'          => $labels          // translated strings
      )
    );
  }
    
}
