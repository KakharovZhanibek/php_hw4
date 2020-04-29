<?php

# Основаная функция валидации формы

/**
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
function validateForm($scheme, $form) {
  global $validators;
  $clean = $form;

  foreach   ($clean as $name => $value){
      $clean[$name] = htmlspecialchars($value);
  }
  
  $errors = [];
  // > Ваша реализация
  
    foreach ($scheme as $name =>$rules) {
        
        foreach($rules as $rule){
            if(is_string($rule)){
                $rule=$validators[$rule];
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

$validators=[
    "require"=>"required",
    "clear-extra-spaces"=>"clearExtraSpaces",
    "integer"=>"integer",
    "bool"=>"boolean"
];

# Функции валидации и вспомогательные функции 

// !! Можно менять в соответсвии с вашей идеей архитектуры

// require
// clearExtraSpaces
// integer
// bool
// ...Другие валидаторы, которые вам понадобятся

function required($value){
return[$value,$value==="" ? "Обязательное поле":null];
}
function clearExtraSpaces($value){
    $value=trim($value);
    if($value!==""){
        $value=preg_replace("/ {2,}/"," ",$value);
    }
    return[$value,null];
}
function integer($value){
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
function boolean(){
    return [$value!=="",null];
}

/**
 * Генерирует функцию, котрая проверяет, что число находится в промежутке
 * @param int $min нижняя граница
 * @param int $max верхняя граница
 * @return callable функция-валидатор 
 */
function generateRangeValidator($min = 0, $max = PHP_INT_MAX) {
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
function generateLengthValidator($min = 0, $max = PHP_INT_MAX) {
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
function generateRegExpValidator($regexp) {
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