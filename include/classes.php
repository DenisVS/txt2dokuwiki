<?php

/*
 * @file 
 * Классы
 */


class LineByLine {

  public $fileContent;
  public $arrContent;

  public function stripping($fileContent) {
    $this->fileContent = $fileContent;
    /**
     * В этой части файл разбивается на строки и обрабатывается построчно
     */
    $this->arrContent = explode("\n", $this->fileContent);  // Запихиваем страницу по строкам в массив
    //$contentInFile = '';
    //foreach ($this->content as $key => $val) {
      //$contentInFile .= $val . "\n";
    //}

    return $this->arrContent;
  }

  public function functionName($param) {
    
  }
  
  
  }
