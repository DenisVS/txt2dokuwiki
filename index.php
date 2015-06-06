<?php

/**
 * @file 
 * Главный файл
 * 
 */
include 'include/functions.php';
include 'include/classes.php';
$LineByLine = new LineByLine(); //новый объект
$path = new ControlEdgeSymbol (); //новый объект

if (isset($argv[1])) {
  $path->text = $argv[1];
  $path->symbol = '/';
  $path->symbolSholdBe = 0;
  $path->position = 'END';
  $inDir = $path->controlStartEndSymbol()['text'];
  $lenghtInPrefixPath = $path->controlStartEndSymbol()['lenght']; //НЕ УБИРАТЬ! Из класса нельзя получить, потому что используется многократно!
  echo 'Длина пути к файлу ' . $lenghtInPrefixPath . "\n";
//  var_dump($inDir);

  echo 'Директория исходных файлов ' . $inDir . "\n";
}
else {
  echo "Введите директорию с исходными файлами\n";
  exit();
}

if (isset($argv[2])) {

  $path->text = $argv[2];
  $path->symbol = '/';
  $path->symbolSholdBe = 0;
  $path->position = 'END';
  $outDir = $path->controlStartEndSymbol()['text'];
  //$lenghtOutPrefixPath = $path->controlStartEndSymbol()['lenght'];
  if (!file_exists($outDir)) {
    mkdir($outDir, 0755, true);
  }
  echo 'Директория с обработанными файлами ' . $outDir . "\n";
}
else {
  echo "Введите директорию с обработанными файлами\n";
  exit();
}


if (isset($argv[3])) {

  $path->text = $argv[3];
  $path->symbol = '/';
  $path->symbolSholdBe = 0;
  $path->position = 'END';
  $mediaDir = $path->controlStartEndSymbol()['text'];
  //$lenghtMediaPrefixPath = $path->controlStartEndSymbol()['lenght'];

  if (!file_exists($mediaDir)) {
    mkdir($mediaDir, 0755, true);
  }
  echo 'Директория с медиа файлами ' . $mediaDir . "\n";
}
else {
  echo "Введите директорию с медиа файлами\n";
  exit();
}


$sourceFiles = (getFileList($inDir, TRUE, FALSE, TRUE)); // получаем листинг
var_dump($sourceFiles);
//цикл перебора массива файлов
for ($i = 0; $i < count($sourceFiles); $i++) {
  echo "Обрабатывается " . $sourceFiles[$i]['name'] . "\n";
  //================ Блок определения параметров URL из пути
  $baseName = pathinfo($sourceFiles[$i]['name'], PATHINFO_BASENAME); // файл без пути
  $filename = pathinfo($sourceFiles[$i]['name'], PATHINFO_FILENAME); //расширение отдельно
  $extension = pathinfo($sourceFiles[$i]['name'], PATHINFO_EXTENSION); //расширение отдельно


  $currentFileNameFromRoot = $sourceFiles[$i]['name'];  //фиксируем имя текущего файла
  $currentFileNameInsideDir = mb_substr($currentFileNameFromRoot, $lenghtInPrefixPath + 1); // полный путь текущего файла внутри обрабатываемой директории (inDir)
  // ОТДЕЛЯЕМ ТЕКСТ ОТ МЕДИА
  if ($extension == 'txt') {
    //Если файл непустой 
    if ($sourceFiles[$i]['size'] > 0) {
      echo "Размер > 0!\n";
      $inFileContent = file_get_contents($currentFileNameFromRoot); // дёргаем контент целиком
      //echo "Содержимое файла целиком:\n".$contentInFile."\n";
      $contentInArray = $LineByLine->stripping($inFileContent); //преобразуем содержимое файла в массив
      //echo "Содержимое файла по строкам в массиве:\n"; var_dump($contentInArray); echo "\n";
//===================== НАЧИНАЕМ РАЗБИРАТЬ КОНТЕНТ =================
//    unset($asterisksStrings);
//    unset($headerPresent);
//    foreach ($contentInArray as $key => $val) {
//
//      //извлекаем все строки со звёздочками, содержащие текст
//      if (preg_match('/(^(\*){1,50})(\*?)(.*?)([^*])(\**)\z/sm', $val)) {
//        if ($key < 2) {
//          $headerPresent = TRUE; //Заголовок есть!
//        }
//
//        $asterisksBefore = trim(preg_replace('/(^(\*){1,50})(\*?)(.*?)([^*])(\**)\z/sm', '$1', $val));
//        //echo "String: " . $val . "\n"; //выводим строку со звёздочками
//        $asteriskLenght = (int) strlen($asterisksBefore); //длина "одни звёздочки" в цифрах
//        //echo "Lenght: " . $asteriskLenght . ". Asterisks: " . $asterisksBefore . "\n"; //выводим длину "одни звёздочки"
//        $asterisksStrings[] = $asteriskLenght;
//      }
//      $schitatZvezdy = TRUE;
//      if (preg_match('/(.*)\S\s--\s\S(.*)\z/m', $val) && ($key < 1)) {
//        $headerPresent = TRUE;  // но если в формате man, то точно есть!
//        $schitatZvezdy = FALSE; // И количество максимумов звёзд не считаем, ибо это заголовок!
//        echo $currentFileNameFromRoot . " в формате man\n";
//      }
//    }
//    if (isset($asterisksStrings)) {
//      $minElement = min($asterisksStrings);
//      $maxElement = max($asterisksStrings);
//      //var_dump($asterisksStrings);
//      echo 'Минимальное и максимальное значение: ' . $minElement . "  " . $maxElement . "\n";
//      //exit();
//
//
//      if ($schitatZvezdy == FALSE) {
//        //а теперь подсчитываем количество максимальных значений звёздочек
//        $coutMaxAsterisk = 0;
//        foreach ($asterisksStrings as $key => $val) {
//          if ($maxElement == $val) {
//            $coutMaxAsterisk++;
//          }
//        }
//        if ($coutMaxAsterisk = 0) {
//          echo "Эти звёздочки в максимальном количестве не заголовок!\n";
//          $headerPresent = FALSE; //А вот и нет!
//        }
//      }
//    }
//====================== НИЖЕ СОБИРАЕМ ФАЙЛ И ПИШЕМ ================
      $outFileContent = $LineByLine->assembling($contentInArray);  //возвращаем из массива в неформатированный текст
      //echo "Содержимое файла целиком:\n".$contentInFile."\n";
      //echo 'Текущий файл: ' . $currentFileName . "\n";
      //извлекаем из полного пути+файла имя файла. Пристыковываем выходную директорию и дерево
      $outFilePath = $outDir . "/" . $currentFileNameInsideDir;
      echo "Путь целевого файла " . $outFilePath . "\n";

      $targetFile = fopen($outFilePath, 'a') or die("can't open file");
      fwrite($targetFile, $outFileContent); //выводим в файл
      fclose($targetFile); //закрываем

      echo "-------------------------------------------------\n";
    }
    else {
      echo "Размер = 0!\n";
      //размер нулевой, проверяем, файл или директория
      $path->text = $currentFileNameFromRoot;
      $path->symbol = '/';
      $path->position = 'END';
      $isItDir = $path->checkingForSymbol();
      if ($isItDir == FALSE) {
        echo "Копируемый файл нулевой длины " . $currentFileNameFromRoot . "\n";
        //ЭТО ВСТАВКА, ДЛЯ СОЗДАНИЯ ПУСТЫХ ФАЙЛОВ.      
        $outFilePath = $outDir . "/" . $currentFileNameInsideDir; //извлекаем из полного пути+файла имя файла. Пристыковываем выходную директорию и дерево
        echo "Путь целевого файла " . $outFilePath . "\n";
        $targetFile = fopen($outFilePath, 'a') or die("can't open file"); //создаём, пусть будет?
        fclose($targetFile); //закрываем
        //конец вставки
        echo "-------------------------------------------------\n";
      }
      else {
        // если же директория
        echo 'Копируемая директория ' . $currentFileNameFromRoot . "\n";
        createDir($outDir . "/" . $currentFileNameInsideDir);
        createDir($mediaDir . "/" . $currentFileNameInsideDir);
        echo "-------------------------------------------------\n";
      }
    }
    //=====================ВЫШЕ ФАПЙЛЫ TXT ====================
  }
  else {
    //=======================НИЖЕ ФАЙЛЫ не TXT ====================
    //Если файл непустой 
    if ($sourceFiles[$i]['size'] > 0) {
      echo "Размер > 0!\n";
      //================ БЛОК РАЗБОРА ТИПОВ ФАЙЛОВ ===================
      //если без расширения, определить тип
      if ($extension == '') {
        $filetype = trim(shell_exec('/usr/bin/file -i ' . $sourceFiles[$i]['name'] . ' | /usr/bin/awk \'{print $2}\'')) . "\n";
        echo $sourceFiles[$i]['name'] . " FILETYPE: " . $filetype . "\n";
      }
      //если без имени, но с расширением
      if ($extension != '' && $filename == '') {
        echo "NONAME: " . $sourceFiles[$i]['name'] . "\n";
      }
      //=====================================

      $inFileContent = file_get_contents($currentFileNameFromRoot); // дёргаем контент целиком
      //echo "Содержимое файла целиком:\n".$contentInFile."\n";

      $mediaFileContent = $inFileContent;  // Файл со входа у нас попадает без обработки на выход
      //echo "Содержимое файла целиком:\n".$contentInFile."\n";
      //echo 'Текущий файл: ' . $currentFileName . "\n";
      //извлекаем из полного пути+файла имя файла. Пристыковываем выходную директорию и дерево
      $mediaFilePath = $mediaDir . "/" . $currentFileNameInsideDir;
      echo "Путь целевого файла " . $mediaFilePath . "\n";

      $targetFile = fopen($mediaFilePath, 'a') or die("can't open file");
      fwrite($targetFile, $mediaFileContent); //выводим в файл
      fclose($targetFile); //закрываем

      echo "-------------------------------------------------\n";
    }
    else {
      echo "Размер = 0!\n";
      //размер нулевой, проверяем, файл или директория
      $path->text = $currentFileNameFromRoot;
      $path->symbol = '/';
      $path->position = 'END';
      $isItDir = $path->checkingForSymbol();
      if ($isItDir == FALSE) {
        echo "Копируемый файл нулевой длины " . $currentFileNameFromRoot . "\n";
        //ЭТО ВСТАВКА, ДЛЯ СОЗДАНИЯ ПУСТЫХ ФАЙЛОВ.      
        $mediaFilePath = $mediaDir . "/" . $currentFileNameInsideDir; //извлекаем из полного пути+файла имя файла. Пристыковываем выходную директорию и дерево
        echo "Путь целевого файла " . $mediaFilePath . "\n";
        $targetFile = fopen($mediaFilePath, 'a') or die("can't open file"); //создаём, пусть будет?
        fclose($targetFile); //закрываем
        //конец вставки
        echo "-------------------------------------------------\n";
      }
      else {
        // если же директория
        echo 'Копируемая директория ' . $currentFileNameFromRoot . "\n";
        createDir($mediaDir . "/" . $currentFileNameInsideDir);
        createDir($outDir . "/" . $currentFileNameInsideDir);
        echo "-------------------------------------------------\n";
      }
    }
  }
  unset($currentFileNameFromRoot);  // на всякий случай прибиваем имя текущего файла.
}

