<?php

namespace Plugins\Thesaurus;

use \Typemill\Plugin;

use \sqlite3;
use Symfony\Component\Yaml\Yaml;

class Index extends Plugin
{
  // What for?
  public static function getSubscribedEvents(){}


  // Database search
  public function searchDatabase($dbfile, $search_for)
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
    $filename = $language . '.thesaurus.sqlite';
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

    // Database search
    $meanings = self::searchDatabase($dbfile, $search_for);
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
