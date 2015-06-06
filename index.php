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
  $lenghtInPrefixPath = $path->controlStartEndSymbol()['lenght'];

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
  mkdir($outDir, 0755, true);
  echo 'Директория с обработанными файлами ' . $outDir . "\n";
}
else {
  echo "Введите директорию с обработанными файлами\n";
  exit();
}
echo "---\n";

$sourceFiles = (getFileList($inDir, TRUE, FALSE, TRUE)); // получаем листинг
var_dump($sourceFiles);
//цикл перебора массива файлов
for ($i = 0; $i < count($sourceFiles); $i++) {
  $currentFileName = $sourceFiles[$i]['name'];  //фиксируем имя текущего файла
  //Если файл непустой файлов 
  if ($sourceFiles[$i]['size'] > 0) {
    echo "Размер > 0!\n";
    echo "Обрабатывается " . $sourceFiles[$i]['name'] . "\n";

    $baseName = pathinfo($sourceFiles[$i]['name'], PATHINFO_BASENAME); // файл без пути
    $extension = pathinfo($sourceFiles[$i]['name'], PATHINFO_EXTENSION); //расширение отдельно
    $filename = pathinfo($sourceFiles[$i]['name'], PATHINFO_FILENAME); //расширение отдельно
    //если без расширения, определить тип
    if ($extension == '') {
      $filetype = trim(shell_exec('/usr/bin/file -i ' . $sourceFiles[$i]['name'] . ' | /usr/bin/awk \'{print $2}\'')) . "\n";
      echo $sourceFiles[$i]['name'] . " FILETYPE: " . $filetype . "\n";
    }
    //если без имени, но с расширением
    if ($extension != '' && $filename == '') {
      echo "NONAME: " . $sourceFiles[$i]['name'] . "\n";
    }

    $inFileContent = file_get_contents($sourceFiles[$i]['name']); // дёргаем контент целиком
    //echo "Содержимое файла целиком:\n".$contentInFile."\n";
    $contentInArray = $LineByLine->stripping($inFileContent); //преобразуем содержимое файла в массив
    //echo "Содержимое файла по строкам в массиве:\n"; var_dump($contentInArray); echo "\n";
//======================================
//    unset($asterisksStrings);
//    foreach ($contentInArray as $key => $val) {
//      //$dcdc = $val;
//      //извлекаем все строки со звёздочками, содержащие текст
//      if (preg_match('/(^(\*){1,50})(\*?)(.*?)([^*])(\**)\z/sm', $val)) {
//        $asterisksBefore = trim(preg_replace('/(^(\*){1,50})(\*?)(.*?)([^*])(\**)\z/sm', '$1', $val));
//        //echo "String: " . $val . "\n"; //выводим строку со звёздочками
//        $asteriskLenght = (int) strlen($asterisksBefore); //длина "одни звёздочки" в цифрах
//        //echo "Lenght: " . $asteriskLenght . ". Asterisks: " . $asterisksBefore . "\n"; //выводим длину "одни звёздочки"
//        $asterisksStrings[] = $asteriskLenght;
//      }
//    }
//    if (isset($asterisksStrings)) {
//      $minElement = min($asterisksStrings);
//      $maxElement = max($asterisksStrings);
//      //echo 'Минимальное и максимальное значение: ' . $minElement . "  " . $maxElement . "\n";
//    }
//======================================
    $outFileContent = $LineByLine->assembling($contentInArray);  //возвращаем из массива в неформатированный текст
    //echo "Содержимое файла целиком:\n".$contentInFile."\n";
    //echo 'Текущий файл: ' . $currentFileName . "\n";
    echo 'Длина пути к файлу ' . $lenghtInPrefixPath . "\n";
    //извлекаем из полного пути+файла имя файла. Пристыковываем выходную директорию и дерево
    $outFilePath = $outDir . "/" . mb_substr($currentFileName, $lenghtInPrefixPath + 1);
    echo "Путь целевого файла " . $outFilePath . "\n";

    $targetFile = fopen($outFilePath, 'a') or die("can't open file");
    fwrite($targetFile, $outFileContent); //выводим в файл
    fclose($targetFile); //закрываем

    echo "-------------------------------------------------\n";
  }
  else {
    //размер нулевой, проверяем, файл или директория
    $path->text = $currentFileName;
    $path->symbol = '/';
    $path->position = 'END';
    $isItDir = $path->checkingForSymbol();
    if ($isItDir == FALSE) {
      echo "Это файл нулевой длины!\n";
      echo $currentFileName . "\n";
      echo "-------------------------------------------------\n";

//ЭТО ВСТАВКА, ДЛЯ СОЗДАНИЯ ПУСТЫХ ФАЙЛОВ, ХЕРНЯ, МОЖНО УДАЛИТЬ ЕСЛИ ЧТО.      
//извлекаем из полного пути+файла имя файла. Пристыковываем выходную директорию и дерево
      $outFilePath = $outDir . "/" . mb_substr($currentFileName, $lenghtInPrefixPath + 1);
      echo "Путь целевого файла " . $outFilePath . "\n";
      $targetFile = fopen($outFilePath, 'a') or die("can't open file"); //создаём, пусть будет?
      fclose($targetFile); //закрываем
    }
    else {
      // если же директория
      echo 'Директория на входе ' . $currentFileName . "\n";
      $outDirPath = $outDir . "/" . mb_substr($currentFileName, $lenghtInPrefixPath + 1);
      echo 'Директория на выходе ' . $outDirPath . "\n";
      mkdir($outDirPath, 0755, true); // создаём директорию
      echo "-------------------------------------------------\n";
    }
  }
  unset($currentFileName);  // на всякий случай прибиваем имя текущего файла.
}

