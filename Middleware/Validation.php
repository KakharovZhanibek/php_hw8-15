<?php

/**
 * 1. Добавить объявление namespace  pd\middleware
 */
// > namespace

namespace pd\middleware;

class Validation {
  

    public const VALIDATORS = [
        "require"=>"required",
        "clear-extra-spaces"=>"clearExtraSpaces",
        "integer"=>"integer",
        "bool"=>"boolean"
    ];

  protected $scheme;

  public function __construct(array $scheme) {
    $this->scheme = $scheme;
  }

  /**
   * Основаная метод валидации формы
   * Функция проверяет и нормализует входные данные формы
   * в соотвествии с указанной схемой валидации
   * 
   * !! Функция должна принимать аргументы и возвращать значения в том формате что указа здесь
   * 
   * Если будете реализовывать возможность последовательных валидаторов, не забудьте, 
   * во-первых, что возникновение ошибки в одном из валидаторов должно останавливать проверку текущего поля
   * во-вторых, очищенные значения должны передаваться по цепочке
   * 
   * @param mixed[] $scheme схема валидации 
   * @param string[] $form данные формы 
   * @return array[] Возврвщвет массив нормальизованных данных и массив ошибок
   */
  function validateForm($form) {

    $clean = $form;
  
    foreach   ($clean as $name => $value){
        $clean[$name] = htmlspecialchars($value);
    }
    
    $errors = [];
    
      foreach ($this->scheme as $name =>$rules) {
          
          foreach($rules as $rule){
              
              if(is_string($rule)) {
                  $rule = ["Validation",Validation::VALIDATORS[$rule]];
              }   
  
              [$clean[$name],$error] = call_user_func($rule, $clean[$name]);
  
              if($error){
                  $errors[$name]=$error;
                  break;
              }
          }
      }
  
      return [$clean, $errors];
  }

  function validateModel($class, $model){
    $form=[];
    
    foreach($model as $k => $v){
      $form[$k] = $v;
    }
    
    [$cleanForm, $errors] = $this->validateForm($form);
    
    $clean=new $class();
    
    foreach($cleanForm as $k=>$v){
      $clean[$k]=$v;
    }

    return[$clean,$errors];
  }

  // static function validateModel($model){
    
  //   $class=new ReflectionClass($book);
    
  //   $validation= $class->getMethod("getValidation")->invoke($model);
    
  //   $validation->validateModel($class->getName(),$model);
  // }

  static function required($value){
    return[$value,$value==="" ? "Обязательное поле":null];
    }
    static function clearExtraSpaces($value){
        $value=trim($value);
        if($value!==""){
            $value=preg_replace("/ {2,}/"," ",$value);
        }
        return[$value,null];
    }
    static function integer($value){
        if($value===""){
            return [$value,null];
        }
        
        $valueInt = (int)$value;
    
        $error=null;
        if(strval($valueInt) !== $value){
          $error= "Поле должен быть числом";
        }
        return[$valueInt,$error];
    }
    static function boolean(){
        return [$value!=="",null];
    }
    
    /**
     * Генерирует функцию, котрая проверяет, что число находится в промежутке
     * @param int $min нижняя граница
     * @param int $max верхняя граница
     * @return callable функция-валидатор 
     */
    static function generateRangeValidator($min = 0, $max = PHP_INT_MAX) {
      return function($value) use ($min, $max) {
        
        if(!is_int($value)){
            return[$value,null];
        }
    
        $error = null;
        if($value<$min||$value>$max){
            $error="Значение должно быть в промежутке от $min до $max";
        }
      
        return [$value, $error];
      };
    }
    
    
    /**
     * Генерирует функцию, котрая проверяет, что длина строки находится в промежутке
     * @param int $min нижняя граница
     * @param int $max верхняя граница
     * @return callable функция-валидатор 
     */
    static function generateLengthValidator($min = 0, $max = PHP_INT_MAX) {
      return function($value) use ($min, $max) {
        if($value===""){
            return[$value,null];
        }
    
        $length=mb_strlen($value);
        $error=null;
        
        if($length<$min||$length>$max){
            $error="Длина строки должна быть в промежутке от $min до $max";
        }
    
        return [$value, $error];
      };
    }
    
    /**
     * Генерирует функцию, котрая проверяет, что значение соответсвует регулярному выражению
     * @param int $regexp регулярное выражение для проверки
     * @return callable функция-валидатор 
     */
    static function generateRegExpValidator($regexp) {
      return function($value) use ($regexp) {
        if($value===""){
            return[$value,null];
        }
    
        $error = null;
    
        if(!preg_match($regexp,$value)){
            $error="Строка должна соответствовать формату $regexp";
        }
      
        return [$value, $error];
      };
    }
}