<?php

namespace pd\models;

class Book {

  public $id;
 
  public $title;

  public $author;

  public $isbn;

  public $year;

  public $cover;

  function __construct() {
    
  }

  public static function getValidation(){
    
    return new Validation([
    
      "title" => [
          "clear-extra-spaces", 
          "require"
        ],
      
        "author" =>  [
          "clear-extra-spaces", 
          "require", 
          function ($v) {
            return[mb_convert_case($v,MB_CASE_TITLE),null];
          }
        ],
      
        "year" => [
          "clear-extra-spaces",
          "integer",
          Validation::generateRangeValidator(1500, 2020) 
        ],
      
        "isbn" => [
          "clear-extra-spaces",
          Validation::generateLengthValidator(13, 13), 
          Validation::generateRegExpValidator("/^(\d+-){3}[\dX]$/")
        ]
  
    ]);
    
  }

}