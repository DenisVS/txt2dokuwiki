<?php

/**
 * @file 
 * Главный файл
 * 
 * 
 */
include 'include/functions.php';
include 'include/classes.php';

if (isset($argv[1])) {
  $lenghtInPath = mb_strlen($argv[1]);
  $pos = mb_strpos($argv[1], "/", $lenghtInPath - 1); // Есть ли в последней позиции слэш
  echo $lenghtInPath . "  это введённая длина исходной директории\n";
  echo $pos . "  это позиция слэша\n";
  if ($pos === false) {
    $inDir = $argv[1];
  }
  else {
    $inDir = substr($argv[1], 0, $lenghtInPath - 1);    // отрубаем последний слэш  
    $lenghtInPath = $lenghtInPath - 1; // и длину приводим в соответствии с действительностью
  }
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

$sourceFiles = (getFileList($inDir, TRUE)); // получаем листинг
var_dump($sourceFiles);
print_r($sourceFiles);
//цикл перебора массива файлов
for ($i = 0; $i < count($sourceFiles); $i++) {
  //Если файл непустой файлов 
  if ($sourceFiles[$i]['size'] > 0) {
    echo "Обрабатывается " . $sourceFiles[$i]['name'] . "\n";
    $contentInFile = file_get_contents($sourceFiles[$i]['name']); // дёргаем контент целиком
    //echo "Содержимое файла целиком:\n".$contentInFile."\n";
    $LineByLine = new LineByLine(); //новый объект
    $contentInArray = $LineByLine->stripping($contentInFile); //Получаем содержимое файла по строкам в массиве
    //echo "Содержимое файла по строкам в массиве:\n"; var_dump($contentInArray); echo "\n";

    
    $contentInFile = FALSE;
    
    /// @TODO Переделать в метод, вынести в класс
    foreach ($contentInArray as $key => $val) {
      $contentInFile .= $val . "\n";
    }
    $contentInFile = substr($contentInFile, 0, -1 );  ///< Отрубаем последний перенос
    //echo "Содержимое файла целиком:\n".$contentInFile."\n";

    
    
    echo 'Файл из массива '.$sourceFiles[$i]['name']."\n";
    echo 'Длина пути к файлу '.$lenghtInPath."\n";
    //извлекаем из полного пути+файла имя файла. Пристыковываем выходную директорию и дерево
    echo '$outDir ='.$outDir."\n";
    $outFilePath = $outDir . "/" . mb_substr($sourceFiles[$i]['name'], $lenghtInPath + 1);
    echo "Путь целевого файла " . $outFilePath . "\n";

    $targetFile = fopen($outFilePath, 'a') or die("can't open file");
    fwrite($targetFile, $contentInFile); //выводим в файл
    fclose($targetFile); //закрываем

    echo "-------------------------------------------------\n";
  } else {
    //Если нулевой длины, проверяем, директория ли
    $pos = mb_strpos($sourceFiles[$i]['name'], "/", $lenghtOutPath, "CP1251"); // Есть ли в последней позиции слэш
    
    if ($pos === false) {
      echo "Это файл нулевой длины\n";
    }
    else {
      echo '$pos = '.$pos."\n";
      echo '$lenghtOutPath = '.$lenghtOutPath."\n";
      echo $lenghtInPath . ' Директория из массива ' . $sourceFiles[$i]['name'] . "\n";
      // если же директория
      $outDirPath = $outDir . "/" . mb_substr($sourceFiles[$i]['name'], $lenghtInPath + 1);
      echo "Путь целевой директории " . $outDirPath . "\n";
      mkdir($outDirPath, 0755, true); // создаём директорию
    }
  }
}
