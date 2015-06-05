<?php

/**
 * @file 
 * Главный файл
 * 
 */
include 'include/functions.php';
include 'include/classes.php';
$LineByLine = new LineByLine(); //новый объект
$inPath = new ControlEdgeSymbol (); //новый объект

if (isset($argv[1])) {
  $inPath->text = $argv[1];
  $inPath->symbol = '/';
  $inPath->symbolSholdBe = 0;
  $inPath->position = 'END';
  $inDir = $inPath->controlStartEndSymbol()['text'];
  $lenghtInPrefixPath = $inPath->controlStartEndSymbol()['lenght'];

//  var_dump($inDir);

  echo 'Директория исходных файлов ' . $inDir . "\n";
}
else {
  echo "Введите директорию с исходными файлами\n";
  exit();
}

if (isset($argv[2])) {
  $lenghtOutPath = mb_strlen($argv[2]);
  $pos = mb_strpos($argv[2], "/", $lenghtOutPath - 1); // Есть ли в последней позиции слэш
  echo $lenghtOutPath . "  это введённая длина целевой директории\n";
  echo $pos . "  это позиция слэша\n";
  if ($pos === false) {
    $outDir = $argv[2];
  }
  else {
    $outDir = substr($argv[2], 0, $lenghtOutPath - 1);    // отрубаем последний слэш     
    $lenghtOutPath = $lenghtOutPath - 1; // и длину приводим в соответствии с действительностью
  }
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
    //echo 'Файл из массива ' . $sourceFiles[$i]['name'] . "\n";
    echo 'Длина пути к файлу ' . $lenghtInPrefixPath . "\n";
    //извлекаем из полного пути+файла имя файла. Пристыковываем выходную директорию и дерево
    $outFilePath = $outDir . "/" . mb_substr($sourceFiles[$i]['name'], $lenghtInPrefixPath + 1);
    echo "Путь целевого файла " . $outFilePath . "\n";

    $targetFile = fopen($outFilePath, 'a') or die("can't open file");
    fwrite($targetFile, $outFileContent); //выводим в файл
    fclose($targetFile); //закрываем

    echo "-------------------------------------------------\n";
  }
  else {
    //Если нулевой длины, проверяем, директория ли? Ищем в конце слэш.
    $pos = mb_strpos($sourceFiles[$i]['name'], "/", mb_strlen($sourceFiles[$i]['name']) - 1);
    if ($pos === false) {
      echo "Это файл нулевой длины!\n";
      echo $sourceFiles[$i]['name'] . "\n";
      echo "-------------------------------------------------\n";
    }
    else {
      // если же директория
      echo 'Директория на входе ' . $sourceFiles[$i]['name'] . "\n";
      $outDirPath = $outDir . "/" . mb_substr($sourceFiles[$i]['name'], $lenghtInPrefixPath + 1);
      echo 'Директория на выходе ' . $outDirPath . "\n";
      mkdir($outDirPath, 0755, true); // создаём директорию
      echo "-------------------------------------------------\n";
    }
  }
}

