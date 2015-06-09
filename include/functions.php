<?php

/**
 * @file Функции
 * 
 */

/**
 *  Сканирование директорий и получение списка объектов
 * @param string $dir Директория для сканирования
 * @param boolean $recurse Рекурсивнаое сканирование
 * @param int $depth Глубина (false = без ограничений)
 * @param boolean $hidden Учитывать скрытые файлы (с точки). По умолчанию FALSE
 * 
 * array['fields']  
 *  [fieldName] 
 *    ['name'] путь к файлу от текущего уровня
 *    ['size'] размер файла (диретории) в байтах
 *    ['lastmod'] время последней модификации файла(директории) в UNIXTIME
 * @return array(string|int|int)[]  $retval (See above)
 * 
 *  */
function getFileList($dir, $recurse = FALSE, $depth = FALSE, $hidden = FALSE) {
  // массив, хранящий возвращаемое значение
  $retval = array();

  // добавить конечный слеш, если его нет
  if (substr($dir, -1) != "/")
    $dir .= "/";

  // указание директории и считывание списка файлов
  $d = @dir($dir) or die("getFileList: Не удалось открыть каталог $dir для чтения");
  while (false !== ($entry = $d->read())) {

    // пропустить скрытые файлы
    if (($entry[0] == "." && $hidden == FALSE) OR $entry == "." OR $entry == "..")
      continue;
    if (is_dir("$dir$entry")) {
      $retval[] = array(
        "name" => "$dir$entry/",
        "size" => 0,
        "lastmod" => filemtime("$dir$entry")
      );
      if ($recurse && is_readable("$dir$entry/")) {
        if ($depth === false) {
          $retval = array_merge($retval, getFileList("$dir$entry/", TRUE, FALSE, $hidden));
        }
        elseif ($depth > 0) {
          $retval = array_merge($retval, getFileList("$dir$entry/", TRUE, $depth - 1, $hidden));
        }
      }
    }
    elseif (is_readable("$dir$entry")) {
      $retval[] = array(
        "name" => "$dir$entry",
        "size" => filesize("$dir$entry"),
        "lastmod" => filemtime("$dir$entry")
      );
    }
  }
  $d->close();

  return $retval;
}

/**
 * 
 * Функция обрезания текста по вхождениям
 * @param string $text Текст на входе
 * @param string $startEntry Начальное вхождение
 * @param string $endEntry Конечное вхождение
 * @param boolean $includeStart Устарело
 * @param boolean $includeEnd Устарело
 * @return string Текст на выходе
 * @todo Разобраться с устаревшими параметрами
 */
function truncateText($text, $startEntry, $endEntry, $includeStart = FALSE, $includeEnd = FALSE) {
  $lenghtStartEntry = mb_strlen($startEntry);
  $lenghtEndEntry = mb_strlen($endEntry);

//    if ($includeStart == TRUE) {
//        $positionStart = mb_strpos($text, $startEntry);
//    } else {
//        $positionStart = mb_strpos($text, $startEntry) + $lenghtStartEntry;
//    }


  if ($startEntry == NULL) {
    $positionStart = 0;  //
  }
  else {
    $positionStart = mb_strpos($text, $startEntry) + $lenghtStartEntry;
  }

  if ($endEntry == NULL) {
    $result = trim(mb_substr($text, $positionStart));  //
  }
  else {
    $positionEnd = mb_strpos($text, $endEntry, $positionStart);
    //если же вхождение не найдено
    if ($positionEnd == NULL) {
      $result = trim(mb_substr($text, $positionStart));
    }
    else {
      $result = trim(mb_substr($text, $positionStart, $positionEnd - $positionStart));  //
    }
  }


  return $result;
}

/**
 *
 * Функция вырезания текста по вхождениям
 * @param string $text Текст на входе
 * @param string $startEntry Начальное вхождение
 * @param string $endEntry Конечное вхождение
 * @return string Текст на выходе
 * 
 */
function removeExcess($text, $startEntry, $endEntry) {
  $lenghtStartEntry = mb_strlen($startEntry);
  $lenghtEndEntry = mb_strlen($endEntry);
  if ($startEntry == NULL) {
    $positionStart = 0;  //
  }
  else {
    //$positionStart = mb_strpos($text, $startEntry) + $lenghtStartEntry;
    $positionStart = mb_strpos($text, $startEntry);
  }
  $contentHead = mb_substr($text, 0, $positionStart);


  $contentTail = mb_substr($text, $positionStart);

  $tailLenght = mb_strlen($contentTail);
  if ($endEntry == NULL) {
    $positionEnd = $tailLenght;
  }
  else {
    //$positionStart = mb_strpos($text, $startEntry) + $lenghtStartEntry;
    $positionEnd = mb_strpos($contentTail, $endEntry);
  }
  $contentTail = mb_substr($contentTail, $positionEnd + $lenghtEndEntry);


  $result = $contentHead . $contentTail;
  return $result;
}

/**
 * Функция проверки наличия слэша в конце строки
 * @param string $text Проверяемая строка
 * @return boolean
 */
function checkingForSlash($text) {
  $pos = mb_strpos($text, "/", mb_strlen($text) - 1);
  if ($pos === false) {
    return FALSE;
  }
  else {
    return TRUE;
  }
}

function createDir($path) {
  if (!file_exists($path)) {
    echo 'Создаваемая директория ' . $path . "\n";
    mkdir($path, 0755, true); // создаём директорию
  }
}

function minMaxValues($array, $zero = NULL) {
  $count['min']['value'] = min($array);
  $count['max']['value'] = max($array);
  //Учитывать ли нули
  if ($zero == NULL) {
    $count['min']['value'] = max($array);
    foreach ($array as $key => $value) {
      if ($value < $count['min']['value'] && $value != 0) {
        $count['min']['value'] = $value;
      }
    }
  }
  //var_dump($array);
  echo 'Минимальное значение: ' . $count['min']['value'] . "\n";
  echo 'Максимальное значение: ' . $count['max']['value'] . "\n";
  $count['max']['quantity'] = 0;
  $count['min']['quantity'] = 0;
  foreach ($array as $key => $val) {
    if ($count['max']['value'] == $val) {
      $count['max_indexes'][] = $key;
      $count['max']['quantity'] ++; //считаем, сколько ключей с максимальным значением
    }
  }
  foreach ($array as $key => $val) {
    if ($count['min']['value'] == $val) {
      $count['min_indexes'][] = $key;
      $count['min']['quantity'] ++; //считаем, сколько ключей с минимальным значением
    }
  }
  return $count;
}

function lenghtEntryAsterisks($param) {
  $asteriskLenght = 0;
  //извлекаем все строки со звёздочками, содержащие текст
  if (preg_match('/(^(\*){1,50})(\*?)(.*?)([^*])(\**)\z/sm', $param)) {
    $asterisksBefore = trim(preg_replace('/(^(\*){1,50})(\*?)(.*?)([^*])(\**)\z/sm', '$1', $param));
    $asteriskLenght = (int) strlen($asterisksBefore); //длина "одни звёздочки" в цифрах
  }
  return $asteriskLenght;
}

/**
 * Функция замены строк со звёздами на равенства
 * @param type $inFileArray
 * @param type $asterisksAndEqual
 * @return string
 */
function replaceAsterisksToEqual($inFileArray, $asterisksAndEqual) {
  foreach ($inFileArray as $string) {
    //если сплошные звёзды
    if (preg_match('/(\A\*{1,}\z)/m', $string)) {
      $string = preg_replace('/(\A\*{1,}\z)/m', '----', $string); //заменить на черту
    } //а если текст со звёздами в начале
    elseif (preg_match('/(^(\*){1,50})(\*?)(.*?)([^*])(\**)\z/sm', $string)) {
      $asterisksBefore = trim(preg_replace('/(^(\*){1,50})(\*?)(.*?)([^*])(\**)\z/sm', '$1', $string));
      $asteriskLenght = (int) strlen($asterisksBefore); //длина "одни звёздочки" в цифрах
      $string = trim(preg_replace('/(^(\*){1,50})(\*?)(.*?)([^*])(\**)\z/m', '$4$5', $string));
      $equalsLenght = $asterisksAndEqual[$asteriskLenght]; // выборка  из массива
//формируем значки равенства
      $equals = '';
      for ($index = 0; $index < $equalsLenght; $index++) {
        $equals .= '=';
      }
      $string = $equals . ' ' . $string . ' ' . $equals;
      //echo $string . " EQ\n";
    }
    $return[] = $string;
  }
  return $return;
}

function manStyle($param) {
  if (preg_match('/(.*)\S\s--\s\S(.*)\z/m', $param)) {
    return TRUE;
  }
  else {
    return FALSE;
  }
}

/**
 * Аналог str_replace для мультибайтных строк
 * @param string $needle Вхождение
 * @param string $replacement Замена
 * @param string $haystack Строка на входе
 * @return string Строка на выходе
 */
function mb_str_replace($needle, $replacement, $haystack) {
  $needle_len = mb_strlen($needle);
  $replacement_len = mb_strlen($replacement);
  $pos = mb_strpos($haystack, $needle);
  while ($pos !== false) {
    $haystack = mb_substr($haystack, 0, $pos) . $replacement
        . mb_substr($haystack, $pos + $needle_len);
    $pos = mb_strpos($haystack, $needle, $pos + $replacement_len);
  }
  return $haystack;
}

/**
 * Функция подъёма по директориям выше на заданное количество ступеней (слэшей). 
 * Обрубает лишние низлежащие. Дефолтно 1 уровень.
 * @param string $url Исходный URL
 * @param int $level Уровень, на который надо подняться
 * @return string Результирующий URL
 */
function dirUp($url, $level = 1) {
  $i = 0;
  do {
    $pos = mb_strrpos($url, '/');
    $url = mb_substr($url, 0, $pos);
    $i++;
  } while ($level > $i);
  return $url;
}

function insertCherezOdin($param) {
  foreach ($param as $key => $val) {
    $out[] = $val;
    $out[] = '';
  }
  return $out;
}
