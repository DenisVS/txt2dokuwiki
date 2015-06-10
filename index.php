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
//var_dump($sourceFiles);
//цикл перебора массива файлов
for ($i = 0; $i < count($sourceFiles); $i++) {
  echo "Обрабатывается " . $sourceFiles[$i]['name'] . "\n";
  //================ Блок определения параметров URL из пути
  $baseName = pathinfo($sourceFiles[$i]['name'], PATHINFO_BASENAME); // файл без пути
  $filename = pathinfo($sourceFiles[$i]['name'], PATHINFO_FILENAME); //имя отдельно
  $extension = pathinfo($sourceFiles[$i]['name'], PATHINFO_EXTENSION); //расширение отдельно
  $updirName = pathinfo(dirUp($sourceFiles[$i]['name'], 1), PATHINFO_BASENAME); // вышележащая директория без пути


  $currentFileNameFromRoot = $sourceFiles[$i]['name'];  //фиксируем имя текущего файла
  $currentFileNameInsideDir = mb_substr($currentFileNameFromRoot, $lenghtInPrefixPath + 1); // полный путь текущего файла внутри обрабатываемой директории (inDir)
  //======= с именами на выходе разберёмся…
  $currentOutFileNameInsideDir = prettyPath($currentFileNameInsideDir); 
  $currentOutFileNameFromRoot = prettyPath($currentFileNameFromRoot); 
  
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
      unset($asterisksStrings);
      unset($headerPresent);
      $header = FALSE;

      //== РАЗБОР МАССИВА С КОНТЕНТОМ ПОСТРОЧНО. КОЛИЧЕСТВО ЗВЁЗД В КАЖДОЙ СТРОКЕ.
      foreach ($contentInArray as $key => $val) {
        $asterisksStrings[] = lenghtEntryAsterisks($val); //загоняем в массив количество звёзд в начале строки
        if (manStyle($val) == TRUE && $key < 2) {
          $header = $val;
        }
      }
      $analysysOfAstarisks = minMaxValues($asterisksStrings);
      //var_dump($analysysOfAstarisks);
      //var_dump($asterisksStrings);
      //==/КОНЕЦ РАЗБОРА МАССИВА С КОНТЕНТОМ
      //      
      //      
      //      
      ////============      // разберёмся с соотношениями количеств звёзд
      //echo 'MIN: '.$analysysOfAstarisks['min']['value']."\n";
      if ($header == FALSE) {
        echo 'Количество максимумов ' . $analysysOfAstarisks['max']['quantity'] . "\n";

        if (max($asterisksStrings) > 0 && $analysysOfAstarisks['max']['quantity'] == 1 && $analysysOfAstarisks['max_indexes'][0] < 2) {
          //echo "Звёздочки в максимальном количестве не заголовок, их " . $coutMaxAsterisk . "!\n";
          $header = preg_replace('/(^(\*){1,50})(\*?)(.*?)([^*])(\**)\z/m', '$4$5', trim($contentInArray[$analysysOfAstarisks['max_indexes'][0]])); //обрубаем звёздочки у заголовка
          $numHeaderString = $analysysOfAstarisks['max_indexes'][0];
          echo 'Строка заголовка ' . $numHeaderString . "\n";
          unset($contentInArray[$numHeaderString]); // Убиваем строку с заголовком
        }
      }

      // если нет заголовка и есть 1-я строка
      if ($header == FALSE && isset($contentInArray[1])) {
        //если нет (/ * < >) и (0 строка с содержимым) и (1 строка пустая)
        if ((strpos($contentInArray[0], '*') === false) && (strpos($contentInArray[0], '/') === false) && (strpos($contentInArray[0], '<') === false) && (strpos($contentInArray[0], '>') === false) && (strpos($contentInArray[0], '{') === false) && (strpos($contentInArray[0], '}') === false) && (strpos($contentInArray[0], ':') === false) && trim($contentInArray[0]) != FALSE && trim($contentInArray[1]) == FALSE) {
          echo 'Это первая строка: ' . $contentInArray[0] . "\n";
          $header = trim($contentInArray[0]);
          $numHeaderString = 0;
          echo 'Строка заголовка ' . $numHeaderString . "\n";
          unset($contentInArray[0]); // Убиваем строку с заголовком
        }
      }

      if ($header == FALSE) {
        $header = trim(mb_str_replace('_', ' ', $updirName) . ' - ' . mb_str_replace('_', ' ', $filename));
        $numHeaderString = NULL;
      }

      echo 'Заголовок: "' . $header . '"  ' . "\n";
      array_unshift($contentInArray, '====== ' . $header . ' ======'); // вначале вставляем заголовок
      //var_dump($contentInArray);
      $contentInArray = insertCherezOdin($contentInArray); // разреживаем контент черезстрочно
      //      
      //== РАЗБОР МАССИВА С КОНТЕНТОМ ПОСТРОЧНО. КОЛИЧЕСТВО ЗВЁЗД В КАЖДОЙ СТРОКЕ ПОСЛЕ ПОДМЕНЫ ПЕРВОЙ СТРОКИ.
      // В этом блоке делаем массив с количеством звёзд по убыванию
      unset($asterisksStrings);
      foreach ($contentInArray as $key => $val) {
        $asterisksStrings[] = lenghtEntryAsterisks($val); //загоняем в массив количество звёзд в начале строки
      }
      //очистка от пустых строк (использовать вместе!)
      $asterisksStrings = array_filter($asterisksStrings);
      sort($asterisksStrings);
      $asterisksStrings = array_unique($asterisksStrings); //уникализируем значения
      // теперь делаем массив размером 5, по количеству уровней после главного заголовка      
      while (count($asterisksStrings) < 6) {
        $asterisksStrings[] = 0;
      }
      sort($asterisksStrings); //сортируем массив
      $asterisksStrings = array_flip($asterisksStrings); //меняем ключи со значениями 
      //==/КОНЕЦ РАЗБОРА МАССИВА С КОНТЕНТОМ. На выходе массив ключ = звёзды, значение = равенства
      $contentInArray = replaceAsterisksToEqual($contentInArray, $asterisksStrings); //меняем звёзды на равенства
      //
      //

      //
//====================== НИЖЕ СОБИРАЕМ ФАЙЛ И ПИШЕМ ================
      $outFileContent = $LineByLine->assembling($contentInArray);  //возвращаем из массива в неформатированный текст
      //echo "Содержимое файла целиком:\n".$contentInFile."\n";
      //echo 'Текущий файл: ' . $currentFileName . "\n";
      //извлекаем из полного пути+файла имя файла. Пристыковываем выходную директорию и дерево
      $outFilePath = $outDir . "/" . $currentOutFileNameInsideDir;
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
        $outFilePath = $outDir . "/" . $currentOutFileNameInsideDir; //извлекаем из полного пути+файла имя файла. Пристыковываем выходную директорию и дерево
        echo "Путь целевого файла " . $outFilePath . "\n";
        $targetFile = fopen($outFilePath, 'a') or die("can't open file"); //создаём, пусть будет?
        fclose($targetFile); //закрываем
        //конец вставки
        echo "-------------------------------------------------\n";
      }
      else {
        // если же директория
        echo 'Копируемая директория ' . $currentFileNameFromRoot . "\n";
        createDir($outDir . "/" . $currentOutFileNameInsideDir);
        createDir($mediaDir . "/" . $currentOutFileNameInsideDir);
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
      $mediaFilePath = $mediaDir . "/" . $currentOutFileNameInsideDir;
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
        $mediaFilePath = $mediaDir . "/" . $currentOutFileNameInsideDir; //извлекаем из полного пути+файла имя файла. Пристыковываем выходную директорию и дерево
        echo "Путь целевого файла " . $mediaFilePath . "\n";
        $targetFile = fopen($mediaFilePath, 'a') or die("can't open file"); //создаём, пусть будет?
        fclose($targetFile); //закрываем
        //конец вставки
        echo "-------------------------------------------------\n";
      }
      else {
        // если же директория
        echo 'Копируемая директория ' . $currentFileNameFromRoot . "\n";
        createDir($mediaDir . "/" . $currentOutFileNameInsideDir);
        createDir($outDir . "/" . $currentOutFileNameInsideDir);

        //============== СОЗДАНИЕ start.txt в директории



        if ($handle = opendir($inDir . "/" . $currentFileNameInsideDir)) {
          echo "Дескриптор каталога: $handle\n";
          echo "Записи:\n";

          /* чтения элементов каталога */
          while (false !== ($entry = readdir($handle))) {
            // если   не диреткория
            if (!is_dir($inDir . "/" . $currentFileNameInsideDir . $entry)) {

              $attachExtension = pathinfo($entry, PATHINFO_EXTENSION);

              if ($attachExtension != 'txt' && trim($entry) != '.' && trim($entry) != '..') {
                echo "$entry\n";
                $ф = prettyPath($entry);
                echo "$ф\n";

                if ($attachExtension == '') {

                  echo "расширения нет!\n";
                }
              }
            }
          }
          closedir($handle);
        }

        $startFileContent = "====== " . mb_strtoupper(pathinfo(dirUp($currentOutFileNameInsideDir), PATHINFO_BASENAME)) . ":INDEX ======\n" . '{{filelist>*&sort=name}}';
        $targetFile = fopen($outDir . "/" . $currentOutFileNameInsideDir . '/start.txt', 'a') or die("can't open file"); //создаём
        fwrite($targetFile, $startFileContent); //выводим в файл
        fclose($targetFile); //закрываем
        //========= /END СОЗДАНИЕ start.txt в директории




        echo "-------------------------------------------------\n";
      }
    }
  }
  unset($currentFileNameFromRoot);  // на всякий случай прибиваем имя текущего файла.
}

